<?php

if (isset($_POST['fullname'])) {
  $previousGeneration = [];
  if (isset($_POST['result'])) {
    $previousGeneration = json_decode($_POST['result']);
  }
  if (is_null($previousGeneration)) {
    $previousGeneration = [];
  }
  $previousGeneration[] = [
    'fullName' => $_POST['fullname'],
    'hostName' => $_POST['hostName'],
    'isWitness' => boolval($_POST['isWitness']),
    'invitations' => $_POST['invitations']
  ];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Add guest</title>
  <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
  <style>
  html {
    background-color: #000000;
    color: #00ff2b;
  }
    input {
      margin: 10px;
      padding: 5px;
    }
    input {
      background-color: #00ff2b;
      color: #000000;
      padding: 5px;
      border: 1px solid #000000;
    }
    button {
      padding: 5px;
      background-color: #00ff2b;
      color: #000000;
      border: 1px solid #000000;
    }
    label {
      margin: 10px;
    }
    body {
      font-size: 12px;
      font-family: 'Source Code Pro', serif;
    }
  </style>
  <script type="text/javascript">
    function prettyPrint() {
      var ugly = document.getElementById('result').value;
      var obj = JSON.parse(ugly);
      var pretty = JSON.stringify(obj, undefined, 4);
      document.getElementById('result').value = pretty;
    }
  </script>
</head>
<body onload="javascript: prettyPrint();">
  <form method="post">
    <fieldset>
      <legend>Add new guest</legend>
      <p>
        <label>Guest full name</label>
        <input type="text" name="fullname" size="50">
      </p>

      <p>
        <label>Est témoin?</label>
        <input type="checkbox" name="isWitness" value="1"> Oui
      </p>

      <p>
        <label>Host full name</label>
        <select name="hostName">
          <option value="Christophe de Batz">Christophe de Batz</option>
          <option value="Poppéa de Raimondi">Poppéa de Raimondi</option>
        </select>
      </p>

      <p>
        <label><input type="checkbox" checked="true" name="invitations[]" value="fiancailles"> Fiançailles</label>
        <label><input type="checkbox" checked="true" name="invitations[]" value="mairie"> Mairie</label>
        <label><input type="checkbox" checked="true" name="invitations[]" value="eglise"> Eglise & coktail</label>
        <label><input type="checkbox" checked="true" name="invitations[]" value="diner"> Diner</label>
      </p>

      <p>
        <input type="submit" value="Add new guest...">
      </p>
    </fieldset>
    <div style="margin:auto; margin: 20px;">
      <button onclick="javascript: selectAndCopy(); this.innerHTML='Copied!!!'; return false;" id="button-copy">
        Click to copy
      </button>
    </div>
    <textarea name="result" id="result" cols="150" rows="30" style="font-family: Montserrat; background-color: #000000; color: #00ff2b; font-size: 12px; border: 0px; margin: 20px;">
      <?php if (isset($previousGeneration) && count($previousGeneration) > 0) echo json_encode($previousGeneration); ?>
    </textarea>
  </form>

  <script>
    function selectAndCopy () {
      var result = document.getElementById('result');
      result.select();
      document.execCommand('copy');
      setTimeout(function () {
        document.getElementById('button-copy').innerHTML = 'Click to copy';
      }, 700);
    }
  </script>
</body>
</html>