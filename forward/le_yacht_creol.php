<?php
session_start();
include("../includes/header.php");
?>

<?php
$pdo = new PDO('mysql:host=db5000232553.hosting-data.io;dbname=dbs227125', 'dbu387591', 'brun#ITPM8');

//Für ein anderes Boot einfach die Id verändern
$sql = "SELECT * FROM boots_typen where boots_typ_id = 6";

foreach ($pdo->query($sql) as $row) {
  echo '<div class="container">
                <br>
                <h1>
                    ', $row['bootsmodell'] . "
                </h1>
                <br />
            </div>";
}

//säubert die Daten aus dem Buchungsformular
function test_input($data)
{
  $data = trim($data); //whitespace entfernen
  $data = stripslashes($data); //entfernt alle Backslashes (\) aus Zeichenketten
  $data = htmlspecialchars($data); //Sonderzeichen werden umgewandelt
  return $data;
}
//Fehlercodes für den Fall dass falsche Angaben gemacht werden
$fehlerVorname = $fehlerNachname = $fehlerEmail = $fehlerTelefonnummer = $fehlerAnschrift = $fehlerPLZ = $fehlerOrt = "";

$pregmatchBuchstaben = "/^[a-zA-ZöüäÜÖÄß\-]+$/";
$pregmatchTelefonnummer = "/^[+0-9\s]*$/";
$pregmatchAnschrift = "/^[a-zA-ZäöüÄÖÜß \-\.]+ [0-9]{1,4}[A-Za-z]?$/";
$pregmatchPLZ = "/^[0-9]{5,5}/";
$pregmatchOrt = "/^([a-zA-ZöüäÜÖÄß]+(?:. |-| |'))*[a-zA-ZöüäÜÖÄß]*$/";

//Wenn das Formular abgesendet wurde und einen Wert enthählt, dann...:
if (isset($_POST['buchungsenden'])) {
  //datumsspanne aus daterangepicker
  $date = $_POST["datefilter"];

  //datumsspanne in zwei verschiedene Variablen StartDatumDB und endDatumDB aufteilen
  $string = explode(' - ', $date);
  $date1 = explode('.', $string[0]);
  $date2 = explode('.', $string[1]);

  $startDatumDB = $date1[2] . '-' . $date1[1] . '-' . $date1[0];
  $endDatumDB = $date2[2] . '-' . $date2[1] . '-' . $date2[0];

  //boots_typ_id bei den anderen Boots-Buchungsseiten ändern
  $sqlPreisProWocheHS = "SELECT preis_pro_woche_hs FROM boots_typen WHERE boots_typ_id = 6";
  $sqlPreisProTagHS = "SELECT preis_pro_tag_hs FROM boots_typen WHERE boots_typ_id = 6";
  $sqlPreisProWocheNS = "SELECT preis_pro_woche_ns FROM boots_typen WHERE boots_typ_id = 6";
  $sqlPreisProTagNS = "SELECT preis_pro_tag_ns FROM boots_typen WHERE boots_typ_id = 6";
  $sqlKaution = "SELECT kaution FROM boots_typen WHERE boots_typ_id = 6";

  $db = mysqli_connect('db5000232553.hosting-data.io', 'dbu387591', 'brun#ITPM8', 'dbs227125');

  //Kaution von Bootstyp
  foreach ($db->query($sqlKaution) as $row5) {
    $kaution = $row5['kaution'];
  }

  $a = new DateTime($startDatumDB);
  $b = new DateTime($endDatumDB);

  $preisgesamt;

  $HSanfang = new DateTime('2020-06-01');
  $HSende = new DateTime('2020-09-31');

  //Abstand des Anfangsdatums zum HSanfang
  $difftageanfangHSlinks = $a->diff($HSanfang)->format("%a");
  //Abstand des Enddatums zum HSanfang
  $difftageanfangHSrechts = $b->diff($HSanfang)->format("%a");

  //Abstand des Anfangsdatums zum HSende
  $difftageendeHSlinks = $a->diff($HSende)->format("%a");
  //Abstand des Enddatums zum HSende
  $difftageendeHSrechts = $b->diff($HSende)->format("%a");

  //Wieviele Tage hat der Mietzeitraum
  $difftagegesamt = $a->diff($b)->format("%a");
  //echo "Tage insgesamt", $difftagegesamt."</br>";

  //Wenn mehr Tage in der HS liegen, dann ist $saison = HS und der HSPreis muss genutzt werden
  if ($difftageanfangHSrechts > $difftageanfangHSlinks && $difftageendeHSlinks > $difftageendeHSrechts) {
    //echo "Es ist in der Hauptsaison"."</br>";
    $moduloresult = ($difftagegesamt % 7);
    //echo "Modulo ",$moduloresult."</br>";
    $saison = "HS";

    //wenn es für eine oder mehrere Wochen gebucht wird
    if ($moduloresult == 0) {
      $wochenHS = ($difftagegesamt / 7);

      foreach ($pdo->query($sqlPreisProWocheHS) as $row2) {
        $preisProWocheHS = $row2['preis_pro_woche_hs'];
        //echo "Preis Pro Woche HS ", $preisProWocheHS."</br>";
        $preisgesamt = ($preisProWocheHS * $wochenHS);
        //echo "Preis: ", $preisgesamt ,"€ +", $kaution , "€ (Kaution)"."</br>";
      }
    }
    //wenn Wochen- + Tagespreis kalkuliert und addiert werden muss
    else {
      $tageDieDurchsiebenTeilbarsind = ($difftagegesamt - $moduloresult);
      $anzahlWochenHS = ($tageDieDurchsiebenTeilbarsind / 7);

      foreach ($db->query($sqlPreisProWocheHS) as $row2) {
        $preisProWocheHS = $row2['preis_pro_woche_hs'];
      }

      foreach ($db->query($sqlPreisProTagHS) as $row3) {
        $preisProTagHS = $row3['preis_pro_tag_hs'];
        echo "Preis Pro Tag HS ", $preisProTagHS . "</br>";

        $preis1HS = ($preisProWocheHS * $anzahlWochenHS);
        $preis2HS = ($preisProTagHS * $moduloresult);
        $preisgesamt = ($preis1HS + $preis2HS);
        echo "Preis: ", $preisgesamt, "€ +", $kaution, "€ (Kaution)" . "</br>";
      }
    }
  } else {
    //Nebensaison und Nebensaisonspreis
    //echo "Es befindet sich in Der Nebensaison";
    $moduloresult = ($difftagegesamt % 7);
    //echo "Modulo ",$moduloresult."</br>";
    $saison = "NS";

    //wenn es pro Woche vermietet wird
    if ($moduloresult == 0) {
      $wochenNS = ($difftagegesamt / 7);

      foreach ($db->query($sqlPreisProWocheNS) as $row2) {
        $preisProWocheNS = $row2['preis_pro_woche_ns'];
        //echo "Preis Pro Woche NS ", $preisProWocheNS."</br>";
        $preisgesamt = ($preisProWocheNS * $wochenNS);
        //echo "Preis: ",$preisgesamt,"€ +", $kaution , "€ (Kaution)"."</br>";
      }
    }
    //wenn Wochen- + Tagespreis kalkuliert werden muss
    else {
      $tageDieDurchsiebenTeilbarsind = ($difftagegesamt - $moduloresult);
      $anzahlWochenNS = ($tageDieDurchsiebenTeilbarsind / 7);

      foreach ($db->query($sqlPreisProWocheNS) as $row2) {
        $preisProWocheNS = $row2['preis_pro_woche_ns'];
      }

      foreach ($db->query($sqlPreisProTagNS) as $row3) {
        $preisProTagNS = $row3['preis_pro_tag_ns'];
        //echo "Preis Pro Tag NS ", $preisProTagNS."</br>";

        $preis1NS = ($preisProWocheNS * $anzahlWochenNS);
        $preis2NS = ($preisProTagNS * $moduloresult);
        $preisgesamt = ($preis1NS + $preis2NS);
        //echo "Preis: ",$preisgesamt,"€ +", $kaution , "€ (Kaution)"."</br>";
      }
    }
  }

  //Prüfen ob alle Eingabgefelder vom Buchungsformular enthalten sind und ob die Einträge den Eingabetypen- und Regeln entsprechen
  if (empty($_POST["inputSurname"])) {
    $fehlerVorname = "Vorname wird benötigt";
  } else {
    $vorname = test_input($_POST["inputSurname"]);
    if (!preg_match($pregmatchBuchstaben, $vorname)) {
      // checkt ob Vorname nur Buchstaben und whitespace enthält
      $fehlerVorname = "Nur Buchstaben erlaubt";
    }
  }

  if (empty($_POST["inputName"])) {
    $fehlerNachname = "Nachname wird benötigt";
  } else {
    $nachname = test_input($_POST["inputName"]);
    if (!preg_match($pregmatchBuchstaben, $nachname)) {
      // checkt ob Nachname nur Buchstaben und whitespace enthält
      $fehlerNachname = "Nur Buchstaben erlaubt";
    }
  }

  if (empty($_POST["inputEmail"])) {
    $fehlerEmail = "Email wird benötigt";
  } else {
    $email = test_input($_POST["inputEmail"]);
    // checkt ob E-mailaddresse korrekt ist
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $fehlerEmail = "Falsches E-Mailformat";
    }
  }

  if ($_POST["inputPhone"]) {
    $telefonnummer = test_input($_POST["inputPhone"]);
    //checkt ob Telefonnummer gültig ist
    if (!preg_match($pregmatchTelefonnummer, $telefonnummer)) {
      $fehlerTelefonnummer = "Ungültige Telefonnumer";
    }
  }

  if (empty($_POST["inputStreet"])) {
    $fehlerAnschrift = "Anschrift wird benötigt";
  } else {
    $anschrift = test_input($_POST["inputStreet"]);
    // checkt ob Nachname nur Buchstaben und whitespace enthält
    if (!preg_match($pregmatchAnschrift, $anschrift)) {
      $fehlerAnschrift = "Keine gültige Anschrift";
    }
  }

  if (empty($_POST["inputZip"])) {
    $fehlerPLZ = "PLZ wird benötigt";
  } else {
    $plz = test_input($_POST["inputZip"]);
    // checkt ob Nachname nur Buchstaben und whitespace enthält
    if (!preg_match($pregmatchPLZ, $plz)) {
      $fehlerPLZ = "Keine gültige PLZ";
    }
  }
  if (empty($_POST["inputCity"])) {
    $fehlerOrt = "Ort wird benötigt";
  } else {
    $ort = test_input($_POST["inputCity"]);
    // checkt ob Nachname nur Buchstaben und whitespace enthält
    if (!preg_match($pregmatchOrt, $ort)) {
      $fehlerOrt = "Kein gültiger Ort";
    }
  }
  //Nochmal überprüfen ob alle befüllt und richtig angegeben wurden. Erst dann darf in die DB eingefügt werden
  if (
    //!empty($_POST['inputSurname']) && (preg_match($pregmatchBuchstaben, $vorname)) && 
    //!empty($_POST['inputName']) && (preg_match($pregmatchBuchstaben, $nachname)) && 
    //!empty($_POST['inputStreet']) && (preg_match($pregmatchAnschrift, $anschrift)) && 
    //!empty($_POST['inputZip']) && (preg_match($pregmatchPLZ, $plz)) && 
    //!empty($_POST['inputCity']) && (preg_match($pregmatchOrt, $ort)) && 
    !empty($_POST['inputEmail']) && (filter_var($email, FILTER_VALIDATE_EMAIL)) //&& 
    //!empty($_POST['inputPhone']) && (preg_match($pregmatchTelefonnummer, $telefonnummer)) 
    ) {

    $db = mysqli_connect('db5000232553.hosting-data.io', 'dbu387591', 'brun#ITPM8', 'dbs227125');
    mysqli_query($db, "INSERT INTO kunden (vorname, nachname, strasse_hausnummer, plz, ort, email, telefonnummer) VALUES ('$vorname', '$nachname', '$anschrift', '$plz', '$ort', '$email', '$telefonnummer')");

    //$kunden_id abfragen
    $pdo = new PDO('mysql:host=db5000232553.hosting-data.io;dbname=dbs227125', 'dbu387591', 'brun#ITPM8');
    $sql = "SELECT id FROM kunden ORDER BY id DESC LIMIT 1";
    foreach ($pdo->query($sql) as $row) {
      $kunden_id = $row['id'];
    }

    //Hier wird ausgesucht, welches Boot zu dem Zeitpunkt verfügbar ist
    //WICHTIG die ids müssen auf jeder Boottypseite geändert werden!!!
    $schranke = false;
    $db = mysqli_connect('db5000232553.hosting-data.io', 'dbu387591', 'brun#ITPM8', 'dbs227125');
    $sqlAbfrageBelegt = "SELECT id_20 FROM belegt WHERE datum between '$startDatumDB' and '$endDatumDB'";
    foreach ($db->query($sqlAbfrageBelegt) as $row) {
      if ($row['id_20'] != null) { //Wenn Boot buchbar ist --> Buchungsformular.php
        $boots_id = '';
        break;
      } else {
        $boots_id = 20;
        $schranke = true;
      }
    }


    //kunden_id in buchungen einfügen
    $statement = $pdo->prepare("INSERT INTO buchungen (boot, kunde, kalenderwoche, mietanfang, mietende) VALUES (?, ?, ?, ?, ?)");
    $statement->execute(array($boots_id, $kunden_id, '4', $startDatumDB, $endDatumDB));

    //buchungs_id aus buchungen holen
    $sqlBuchungsid = "SELECT buchungs_id FROM buchungen ORDER BY buchungs_id DESC LIMIT 1";
    foreach ($pdo->query($sqlBuchungsid) as $row) {
      $buchungs_id = $row['buchungs_id'];
    }

    //WICHTIG BEI den verschiedenen Bootstypseiten Case und den Wert $tabelle_boots_id ändern
    switch ($boots_id) {
      case 20:
        $tabelle_boots_id = "id_20";
        break;
    }
    //buchungs_id in belegtfelder einfügen
    $statementbuchungs_id = $pdo->prepare("UPDATE belegt Set " . $tabelle_boots_id . " = ? where datum between ? and ?");
    $statementbuchungs_id->execute(array($buchungs_id, $startDatumDB, $endDatumDB));

    //alertBox nach Buchung
    echo '<script type="text/javascript">alert("Ihre Buchungs-ID: ' . $buchungs_id . '\n"+ "Mietzeitraum: ' . $date . '\n"+"Preis ' . $saison . '"+"(ohne Kaution): ' . $preisgesamt . '€\n" + "Ihre Angaben:\n" +"' . $vorname . '\n" + "' . $nachname . '\n" + "' . $email . '\n")</script>';
    ?>
    <script language="javascript" type="text/javascript">
      document.location = "http://brunnenberger.de/";
    </script>
    <?php
  } else {
    echo "Bitte korrigieren Sie Ihre Angabe";
  }
}
  ?>

<!-- HTML-Konstrukt vom Buchungsformular-->
<div class="row">
  <div class="col-sm-6">
    <div class="card">
      <div class="card-body">
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
          <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
          </ol>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img class="d-block w-100" src="../img/yachten/le_yacht_creol_1.png" alt="First slide">
            </div>
            <div class="carousel-item">
              <img class="d-block w-100" src="../img/yachten/le_yacht_creol_2.png" alt="Second slide">
            </div>
            <div class="carousel-item">
              <img class="d-block w-100" src="../img/yachten/le_yacht_creol_3.png" alt="Third slide">
            </div>
          </div>
          <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="card">
      <div class="card-body">
        <div class="accordion" id="accordionExample">

          <div class="card-header" id="headingTwo">
            <h2 class="mb-0">
              <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                Ihre Buchungsdaten
              </button>
            </h2>
          </div>
          <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
            <div class="card-body">

              <form class="was-validated" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-row">
                  <div class="form-row">
                    <div class="form-group col-md-20">
                      <label for="inputDate">Gewünschter Buchungszeitraum</label>
                      <span class="error">* </span>
                      <input type="text" class="form-control" name="datefilter" id="datefilter" required="'required'" placeholder="Zeitraum wählen">
                      <small id="HShinweis" class="form-text text-muted">
                        Hauptsaison vom 01.06. - 31.09.
                      </small>
                      <script type="text/javascript">
                          // Speichern der belegten Termine in JS Array für DRP
                          <?php
                          // Connection
                          $pdo = new PDO('mysql:host=db5000232553.hosting-data.io;dbname=dbs227125', 'dbu387591', 'brun#ITPM8');
                          // Statement
                          $sql = "SELECT * FROM belegt where id_20 IS NOT NULL";
                          // Die Treffer werden in einem PHP Array gespeichert
                          foreach ($pdo->query($sql) as $row) {
                            $array_PHPbelegt[] = $row['datum'];
                          }
                          ?>
                          // Umwandlung von PHP Array zu JS Array
                          const js_arrayBelegteTermine = <?php echo json_encode($array_PHPbelegt); ?>;

                          $(function() {
                            $('input[name="datefilter"]').daterangepicker({
                              alwaysShowCalenders: true,
                              autoUpdateInput: false,
                              locale: {
                                firstDay: 1,
                                format: "DD.MM.YYYY",
                                separator: " bis ",
                                applyLabel: "Bestätigen",
                                cancelLabel: "Abbrechen",
                                fromLabel: "Von",
                                toLabel: "Bis",
                                customRangeLabel: "Custom",
                                weekLabel: "W",
                                daysOfWeek: [
                                  "So",
                                  "Mo",
                                  "Di",
                                  "Mi",
                                  "Do",
                                  "Fr",
                                  "Sa"
                                ],
                                monthNames: [
                                  "Januar",
                                  "Februar",
                                  "März",
                                  "April",
                                  "Mai",
                                  "Juni",
                                  "Juli",
                                  "August",
                                  "September",
                                  "Oktober",
                                  "November",
                                  "Dezember"
                                ],
                              },
                              "isInvalidDate": function(date) {
                                for (var i = 0; i < js_arrayBelegteTermine.length; i++) {
                                  if (date.format('YYYY-MM-DD') === js_arrayBelegteTermine[i]) {
                                    return true;
                                  }
                                }
                              },
                              minDate: moment(),
                            });

                            /* 
                            Bei Klick auf "Bestätigen" wird überprüft,
                            ob der gewählte Zeitraum einen bereits belegten Termin enthält.
                            Falls ja: gewählter Zeitraum wird NICHT übernommen
                            (falls vorher gültiger Zeitraum ausgewählt wurde, bleibt dieser im Inputfeld)
                            Sonst: gewählter Zeitraum WIRD übernommen
                            */
                            $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
                              var schranke = 0;
                              js_arrayBelegteTermine.forEach(function(item, index) {
                                if (item >= picker.startDate.format('YYYY-MM-DD') && item <= picker.endDate.format('YYYY-MM-DD')) {
                                  $(this).val('');
                                  schranke = 1;
                                }
                              })
                              if (schranke == 0) {
                                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                              }
                            });
                            // Löschen des Inputfeldes bei Klick auf "Abbrechen"
                            $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
                              $(this).val('');
                            });
                          });
                        </script>
                    </div>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="inputSurname">Vorname</label>
                    <span class="error">* <?php echo $fehlerVorname; ?></span>
                    <input type="surname" class="form-control is-invalid" id="inputSurname" name="inputSurname" pattern="^[a-zA-ZöüäÜÖÄß\-]+$" required="'required'">
                  </div>
                  <div class="form-group col-md-6">
                    <label for="inputName">Nachname</label>
                    <span class="error">* <?php echo $fehlerNachname; ?></span>
                    <input type="name" class="form-control is-invalid" id="inputName" name="inputName" pattern="^[a-zA-ZöüäÜÖÄß\-]+$" required="'required'">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-8">
                    <label for="inputEmail">Email-Adresse</label>
                    <span class="error">* <?php echo $fehlerEmail; ?></span>

                    <input type="email" class="form-control is-invalid" id="inputEmail" name="inputEmail" pattern="[a-z0-9._%+!#$%&'*+-/=?^_`{|}~-]+@[a-z0-9.-]+\.[a-z]{2,}$" required="'required'">
                    <small id="emailkleinschreiben" class="form-text text-muted">
                      nur Kleinschreibung
                    </small>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-8">
                    <label for="inputPhone">Telefonnummer</label>
                    <input type="phone" class="form-control is-invalid" id="inputPhone" name="inputPhone" placeholder="z.B. 0171 1... / +49 171 1..." pattern="^[+0-9\s]*$" optional>
                    <small id="telefonnummeroptional" class="form-text text-muted">
                      optional
                    </small>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="inputStreet">Anschrift</label>
                    <span class="error">* <?php echo $fehlerAnschrift; ?></span>
                    <input type="text" class="form-control is-invalid" id="inputStreet" name="inputStreet" placeholder="Musterstraße 1" pattern="^[a-zA-ZäöüÄÖÜß \-\.]+ [0-9]{1,4}[A-Za-z]?$" required="'required'">
                  </div>
                  <div class="form-group col-md-2">
                    <label for="inputZip">PLZ</label>
                    <span class="error">* <?php echo $fehlerPLZ; ?></span>
                    <input type="text" class="form-control is-invalid" id="inputZip" name="inputZip" placeholder="12345" pattern="^[0-9]{5,5}" required="'required'">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="inputCity">Stadt</label>
                    <span class="error">* <?php echo $fehlerOrt; ?></span>
                    <input type="text" class="form-control is-invalid" id="inputCity" name="inputCity" placeholder="Musterstadt" pattern="^([a-zA-ZöüäÜÖÄß]+(?:. |-| |'))*[a-zA-ZöüäÜÖÄß]*$" required="'required'">
                  </div>
                  <p><span class="error">* Pflichtangaben</span></p>
                </div>
                <button type="submit" name="buchungsenden" id="buchungsenden" class="btn btn-primary">Buchung
                  abschließen</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<br>
<br>
<div class="container">
  <p>
    Die absolute Krönung unserer Flotte ist unsere Dreimaster-Yacht „Le Yacht Creol“. 
    Die Yacht wurde 1927 bei Camper & Nicholsons in Gosport, England erbaut. 
    Im zweiten Weltkrieg wurde sie beschlagnahmt und als Hilfsschiff von der britischen Marine eingesetzt. 
    Nach einer verheerenden Seeschlacht wurde die Creol, dann an die Küste von St. Peter-Ording geschwemmt, wo sie nach Kriegsende von Karl-Heinz Knigge restauriert wurde. 
    Das Schiff gilt als eine der schönsten und größten Hochsee-Yachten, die jemals gebaut wurden. 
    Der Dreimast-Stagsegelschoner besitzt einen Rumpf aus Stahlspanten belegt mit 10 cm dicken Teak-Planken. 
    Unterhalb der Wasserlinie ist der Schiffskörper zusätzlich mit Kupferplatten beschlagen. 
    An den drei Masten werden bis zu 10 Segel mit einer Fläche von 2040 m2 geführt.
  </p>
</div>

<div class="container">
  <h2>Ausstattung</h2>


  <?php
  $pdo = new PDO('mysql:host=db5000232553.hosting-data.io;dbname=dbs227125', 'dbu387591', 'brun#ITPM8');

  //WICHTIG Für einen anderen Bootstyp einfach die Id verändern
  $sql = "SELECT * FROM boots_typen where boots_typ_id = 6";
  //und gegebenenfalls "Id = " in "Bootsnamen = " abändern wenn man nach den Namen sortieren und abfragen will
  foreach ($pdo->query($sql) as $row) {
    echo '<table class="table"><tbody><tr>';
    echo '<th scope="row">Preis pro Woche HS:</th><td>', $row['preis_pro_woche_hs'] . "€</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Preis pro Tag HS:</th><td>', $row['preis_pro_tag_hs'] . "€</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Preis pro Woche NS:</th><td>', $row['preis_pro_woche_ns'] . "€</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Preis pro Tag NS:</th><td>', $row['preis_pro_tag_ns'] . "€</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Kaution:</th><td>', $row['kaution'] . "€</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Baujahr:</th><td>', $row['baujahr'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Anzahl Personen:</th><td>', $row['anzahl_personen'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Anzahl Kabinen:</th><td>', $row['anzahl_kabinen'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Kojen:</th><td>', $row['kojen'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Hersteller:</th><td>', $row['hersteller'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Bootstyp:</th><td>', $row['bootstyp'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Länge (Meter):</th><td>', $row['länge_(meter)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Breite (Meter):</th><td>', $row['breite_(meter)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Tiefgang (Meter):</th><td>', $row['tiefgang_(meter)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">WC Anzahl:</th><td>', $row['wc_anzahl'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Größe Frischwassertank (Liter):</th><td>', $row['größe_frischwassertank_(liter)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Flybridge:</th><td>', $row['flybridge'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Duschen:</th><td>', $row['duschen'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Motorleistung (PS):</th><td>', $row['motorleistung_(ps)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Motor Anzahl:</th><td>', $row['motor_anzahl'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Größe Treibstofftank (Liter):</th><td>', $row['größe_treibstofftank_(liter)'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Hauptsegel/Großsegel:</th><td>', $row['hauptsegel/großsegel'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Vorsegel:</th><td>', $row['vorsegel'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Ausstattung Deck:</th><td>', $row['ausstattung_deck'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Navigation:</th><td>', $row['navigation'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Technologie:</th><td>', $row['technologie'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Entertainment:</th><td>', $row['entertainment'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Innenausstattung:</th><td>', $row['innenausstattung'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Skipper inkl.:</th><td>', $row['skipper_inkl.'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Führerscheinpflicht:</th><td>', $row['führerscheinpflicht'] . "</td>";
    echo '</tr>';
    echo '<tr>';
    echo '</tbody>';
    echo '</table>';
  }

  ?>
</div>
<?php
include("../includes/footer.php");
?>