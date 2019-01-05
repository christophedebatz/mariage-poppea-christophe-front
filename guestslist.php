<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Guests list</title>
  <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
  <style>
    body {
      font-size: 12px;
      font-family: 'Source Code Pro', serif;
    }
  </style>
  <script type="text/javascript">
    function show(id) {
      let el = document.getElementById(id);
      if (el.style.display === 'none') {
        el.style.display = 'block';
      } else {
        el.style.display = 'none';
      }
    }
  </script>
</head>
<body onload="javascript: computePricing();">
<?php
$content = file_get_contents('./guests.json');
$guests = json_decode($content);

function hasBeenInvited($invitations, $name) {
  return in_array($name, $invitations);
}

function countBy($hostName, $guests = []) {
  $count = 0;
  $guestsLength = count($guests);
  for ($i = 0; $i < $guestsLength; $i++) {
    if ($guests[$i]->hostName == $hostName) {
      $count++;
    }
  }
  return $count;
}
echo '<h1>Invités (' . count($guests) . ')</h1>';
echo '<hr>';

if (!$guests || count($guests) === 0) {
  echo '<div style="margin: 30px; padding: 20px; border: 1px solid black; color: gray; font-weight: bold;">';
  echo 'Il n\'y a eu aucune invitation pour le moment...';
  echo '</div>';
}

function getGuestScore($a) {
  return (hasBeenInvited($a->invitations, 'eglise') ? 1 : 0) 
  + (hasBeenInvited($a->invitations, 'diner') ? 1 : 0);
}

uasort($guests, function ($a, $b) {
  return getGuestScore($a) < getGuestScore($b) ? 1 : -1;
});

$chrisGuests = array_filter($guests, function ($guest) {
  return $guest->hostName == 'Christophe de Batz' ? $guest : null;
});

$poppeaGuests = array_filter($guests, function ($guest) {
  return $guest->hostName == 'Poppéa de Raimondi' ? $guest : null;
});

echo '<h2><a href="#" onclick="javascript:show(\'christophe\');">Invités de Christophe</a> (' . countBy('Christophe de Batz', $guests) . ')</h2>';
echo '<ul style="padding-left: 0px; display: none; margin-left: 0px;" id="christophe">';
$i = 1;
foreach ($chrisGuests as $guest) {
  $color = '#eee';
  if ($i % 2 === 0) {
    $color = 'lightgray';
  }
  echo '<li style="background-color: ' . $color . '; list-style-type: none; margin: 5px; padding-top: 5px; padding-left: 20px; padding-bottom: 20px; border-radius: 10px;">';
  echo '<strong style="font-size: 14px;">' . $i . ') ' . $guest->fullName . '</strong> - <strong>' . getGuestScore($guest) . ' evènement(s)</strong> [ ';
  echo '<strong>Cocktail:</strong> ' . (hasBeenInvited($guest->invitations, 'eglise') ? 'oui' : 'non') . '</strong> | ';
  echo '<strong>Diner:</strong> ' . (hasBeenInvited($guest->invitations, 'diner') ? 'oui' : 'non') . '</strong>';
  echo ' ]';
  $i++;
}
echo '</ul>';
echo '<h2><a href="#" onclick="javascript:show(\'poppea\');">Invités de Poppéa</a> (' . countBy('Poppéa de Raimondi', $guests) . ')</h2>';
echo '<ul style="padding-left: 0px; display: none; margin-left: 0px;" id="poppea">';
foreach ($poppeaGuests as $guest) {
  $color = '#eee';
  if ($i % 2 === 0) {
    $color = 'lightgray';
  }
  echo '<li style="background-color: ' . $color . '; list-style-type: none; margin: 5px; padding-top: 5px; padding-left: 20px; padding-bottom: 20px; border-radius: 10px;">';
  echo '<strong style="font-size: 14px;">' . $i . ') ' . $guest->fullName . '</strong> - <strong>' . getGuestScore($guest) . ' evènement(s)</strong> [ ';
  echo '<strong>Cocktail:</strong> ' . (hasBeenInvited($guest->invitations, 'eglise') ? 'oui' : 'non') . '</strong> | ';
  echo '<strong>Diner:</strong> ' . (hasBeenInvited($guest->invitations, 'diner') ? 'oui' : 'non') . '</strong>';
  echo ' ]';
  $i++;
}
echo '</ul>';

?>

<h1>Réponses des invités</h1>
<hr>
<?php
$content = file_get_contents('./reservations.json');
$reservations = json_decode($content);

function isParticipating($value) {
  return $value ? 'participe' : 'ne participe pas';
}

if (!$reservations || count($reservations) === 0) {
  echo '<div style="margin: 30px; padding: 20px; border: 1px solid black; color: gray; font-weight: bold;">';
  echo 'Il n\'y a eu aucune réponse des invités pour le moment...';
  echo '</div>';
}

$cocktailDinerCount = 0;
$cocktailCount = 0;

echo '<ul style="padding-left: 0px; margin-left: 0px;">';

$i = 0;
foreach ($reservations as $reservation) {
  if ($reservation->eglise && $reservation->diner) {
    $cocktailDinerCount++;
  } else if ($reservation->eglise && !$reservation->diner) {
    $cocktailCount++;
  }
  $color = '#eee';
  if ($i % 2 === 0) {
    $color = 'lightgray';
  }
  echo '<li style="background-color: ' . $color . '; list-style-type: none; margin: 5px; padding-top: 5px; padding-left: 20px; padding-bottom: 20px; border-radius: 10px;">';
  echo '<h3>' . $reservation->fullName . '</h3>';
  echo '<ul>';
  echo '<li><strong>Cocktail:</strong> ' . isParticipating($reservation->eglise) . '</strong></li>';
  echo '<li><strong>Diner:</strong> ' . isParticipating($reservation->diner) . '</strong></li>';
  echo '<ul style="color: gray; padding: 10px; padding-left: 30px;">';
  echo '<li><strong>Répondu le:</strong> ' . date('d-m-Y H:i:s', $reservation->createdAt) . '</li>';
  echo '<li><strong>Mis à jour le:</strong> ' . date('d-m-Y H:i:s', $reservation->updatedAt) . '</li>';
  echo '</ul>';
  echo '</ul>';
  echo '</li>';
  $i++;
}
echo '</ul>';
?>

<script type="text/javascript">
  function computePricing () {
    let tent = parseInt(document.getElementById('price-tent').value, 10);
    let music = parseInt(document.getElementById('price-music').value, 10);
    let cocktailDiner = parseInt(document.getElementById('price-cocktail-diner').value, 10);
    let cocktail = parseInt(document.getElementById('price-cocktail').value, 10);

    let cocktailDinerCount = <?php echo $cocktailDinerCount; ?>;
    let cocktailCount = <?php echo $cocktailCount; ?>;

    let result = cocktailDinerCount * cocktailDiner + cocktailCount * cocktail + music + tent;
    document.getElementById('price').innerHTML = 'Total = ' + result.toString() + ' EUR.'
    document.getElementById('composition').innerHTML = '<h3>Répartititions des coûts</h3><ul style="list-style-type: none;"><li>Total cocktails + diners = ' + cocktailDinerCount + ' x ' + cocktailDiner + ' EUR = ' + parseInt(cocktailDinerCount * cocktailDiner, 10) + ' EUR.</li>'
      + '<li>Total cocktails seuls = ' + cocktailCount + ' x ' + cocktail + ' EUR = ' + parseInt(cocktailCount * cocktail, 10) + ' EUR.</li></ul>';

  }
</script>

<h1>Simulation de budget ( <?php echo $i; ?> réponse(s) )</h1>
<hr>
<fieldset style="border: 1px solid lightgray; border-radius: 10px;">
  <ul>
    <li>Prix tente: <input type="text" id="price-tent" value="4000" size="4"></li>
    <li>Prix musique: <input type="text" id="price-music" value="800" size="4"></li>
    <li>Prix par personne cocktail + diner: <input type="text" id="price-cocktail-diner" value="60" size="4"></li>
    <li>Prix par personne cocktail: <input type="text" id="price-cocktail" value="50" size="4"></li>
  </ul>
  <p><button onclick="javascript: computePricing();">Compute price</button><p>
</fieldset>

<div style="font-weight: bold; font-size: 14px; text-align: center;" id="composition"></div>
<h2 id="price" style="font-weight: bold; text-align: center; padding: 30px;">Total = 0 EUR.</h2>

</body>
</html>