<?php
error_reporting(E_ALL);
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Origin: *');

$filesPaths = [
    'reservations' => './reservations.json',
    'guests' => './guests.json'
];
$currentUserId = 0;

function loadGuestsList () {
    global $filesPaths;
    $guests = json_decode(file_get_contents($filesPaths['guests']));
    return array_map(function ($guest) {
        return createGuest(
            boolval($guest->isWitness),
            $guest->hostName,
            $guest->fullName,
            $guest->invitations
        );
    }, $guests);
}

function createGuest ($isVip = false, $hostName = 'Christophe de Batz', $fullName, $invitations = []) {
    global $currentUserId;
    $result = new \StdClass();
    $result->fullName = $fullName;
    $result->hostName = $hostName;
    $result->vip = $isVip;
    $result->fiancailles = in_array('fiancailles', $invitations);
    $result->mairie = in_array('mairie', $invitations);
    $result->eglise = in_array('eglise', $invitations);
    $result->diner = in_array('diner', $invitations);
    $result->userId = $currentUserId++;
    return $result;
}

$body = file_get_contents("php://input");

if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
  exit();
}

// at page loading, we display the list of guests
if (stripos($_SERVER['QUERY_STRING'], 'userslist') !== false) {
    response(loadGuestsList());
} 
// user has selected a name, we returns its current status if exists
else if (isset($_GET['userId'])) {
    $userId = intval($_GET['userId']);
    $reservations = json_decode(file_get_contents($filesPaths['reservations']));
    $selectedUser = null;
    try {
        $selectedUser = tryFindGuest(loadGuestsList(), $userId);
    } catch (Exception $exception) {
        response(makeError($exception->getMessage()), 401);
    }
    if (is_null($selectedUser)) {
        response(makeError('guest.not.found'), 400);
    }
    $reservation = tryFindReservation($reservations, $userId);
    if (!$reservation) {
        response(makeError('reservation.not.found'), 404);
    }
    response([
        'reservation' => $reservation,
        'user' => $selectedUser
    ]);
}
// user wants to update or to make another reservation
else if (isset($_GET['bookUserId']) && !is_null($body)) {
    $jsonBody = json_decode($body);
    if (is_null($jsonBody->fiancailles) || is_null($jsonBody->mairie) || is_null($jsonBody->eglise)) {
        response(makeError('invalid.input'), 400);
    }
    $newReservation = [
        'userId' => intval($_GET['bookUserId']),
        'fiancailles' => boolval($jsonBody->fiancailles),
        'mairie' => boolval($jsonBody->mairie),
        'eglise' => boolval($jsonBody->eglise),
        'diner' => boolval($jsonBody->diner),
        'updatedAt' => time()
    ];
    // checks that selected userId is a real guest
    $guests = loadGuestsList();
    $selectedUser = null;
    try {
        $selectedUser = tryFindGuest($guests, $newReservation);
    } catch (Exception $exception) {
        response(makeError($exception->getMessage()), 401);
    }
    if (!$selectedUser) {
        response(makeError('guest.not.found'), 404);
    }
    // load all reservations
    $reservations = json_decode(file_get_contents($filesPaths['reservations']));
    if (is_null($reservations)) {
        $reservations = [];
    }

    // try to find if reservation already exists
    $reservation = tryFindReservation($reservations, $newReservation['userId']);

    // update or add new reservation according to if reservation already here
    if (is_null($reservation)) {
        $reservations = addNewReservation($reservations, $newReservation, $selectedUser);
    } else {
        $reservations = updateReservation($reservations, $newReservation, $selectedUser);        
    }
    
    if (!saveReservations($reservations)) {
       response(makeError());
    }
    response(['success' => true]);
} else {
    response(makeError('service.not.found'), 404);
}



function saveReservations ($reservations) {
    global $filesPaths;
    if (isset($reservations) && !is_null($reservations)) {
        if (file_put_contents($filesPaths['reservations'], json_encode($reservations)) === false) {
            response(error_get_last());
        }
        return true;
    }
    return false;
}

function makeError ($cause) {
    return [
        'success' => false,
        'error' => isset($cause) ? $cause : 'technical.error'
    ];
}

function tryFindReservation ($reservations = [], $userId) {
    for ($i = 0; $i < count($reservations); $i++) {
        $reservation = $reservations[$i];
        if ($reservation->userId === $userId) {
            return $reservation;
        }
    }
    return null;
}

function tryFindGuest ($guests = [], $reservation) {
    for ($i = 0; $i < count($guests); $i++) {
        $guest = $guests[$i];
        // if we found guest by id, ensure he can books like he wanted
        if (is_int($reservation) && $reservation === $guest->userId) {
            return $guest;
        }
        else if ($guest->userId === $reservation['userId']) {
            $guestNotRight = !$guest->mairie && $reservation['mairie'] 
                || !$guest->fiancailles && $reservation['fiancailles'] 
                || !$guest->eglise && $reservation['eglise']
                || !$guest->diner && $reservation['diner'];
            if ($guestNotRight) {
                throw new Exception('not.enough.privileges');
            }
            return $guest;
        }
    }
    return null;
}

function addNewReservation ($reservations = [], $reservation, $guest) {
    $reservation['fullName'] = $guest->fullName;
    $reservation['createdAt'] = time();
    $reservations[] = $reservation;
    return $reservations;
}

function updateReservation ($reservations = [], $reservation, $guest) {
    for ($i = 0; $i < count($reservations); $i++) {
        if ($reservations[$i]->userId === $reservation['userId']) {
            $reservation['fullName'] = $guest->fullName;
            $reservation['updatedAt'] = time();
            $reservations[$i] = $reservation;
            return $reservations;
        }
    }
    return null;
}

function response ($object, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($object);
    exit;
}
?>