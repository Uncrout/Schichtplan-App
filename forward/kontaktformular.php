<?php
session_start();
?>

<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="itpm">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Brunnenberger · Yachting</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.3/examples/album/">
    <!-- Font Firmenname -->
    <link href="https://fonts.googleapis.com/css?family=Satisfy&display=swap" rel="stylesheet">

    <!-- DatePicker -->
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../js/moment.min.js"></script>
    <script type="text/javascript" src="../js/daterangepicker.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .error {
            color: #FF0000;
            font-size: small;
        }
    </style>
    <!-- Custom styles for this template -->
    <link href="../css/album.css" rel="stylesheet">
</head>

<body>
    <header>
        <div class="collapse" id="navbarHeader">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-md-7 py-4">
                        <h4 class="text-black">Über uns</h4>
                        <p class="text-muted">Wir sind ein traditioneller Yacht-Charterer aus Kiel. In unserer Flotte ist für jeden etwas dabei. Unsere fünfköpfige Crew ist jederzeit für Sie erreichbar um Ihnen das beste Segelerlebnis zu bieten.</p>
                        <p class="text-muted"><a href="/forward/mehr_erfahren.php">Mehr erfahren</a></p>
                    </div>
                    <div class="col-sm-4 offset-md-1 py-4">
                        <h4 class="text-black">Kontakt</h4>
                        <p class="text-muted">Für besondere Buchungsanfragen oder Ähnliches empfehlen wir Ihnen unser Kontaktformular:</p>
                        <p class="text-muted"><a href="/forward/kontaktformular.php">Zum Kontaktformular</a></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="navbar shadow-sm">
            <div class="container d-flex justify-content-between">
                <a style="font-family: 'Satisfy', cursive; text-decoration: none; color: #000;" href="../index.php" class="navbar-brand d-flex align-items-center">
                    <strong>Brunnenberger Yachting</strong>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <img src="/img/menu_icon_512.png" width="30" height="30">
                    </span>
                </button>
            </div>
        </div>
    </header>


    <?php
    // Datenbankverbindung für Kontaktformular
    $pdo = new PDO('mysql:host=db5000232553.hosting-data.io;dbname=dbs227125', 'dbu387591', 'brun#ITPM8');
    $db = mysqli_connect('db5000232553.hosting-data.io', 'dbu387591', 'brun#ITPM8', 'dbs227125');
    ?>

    <?php
    //Fehlermeldungen, werden in einer weiter unten mit String gefüllt
    $fehlerVorname = $fehlerNachname = $fehlerEmail = $fehlerTelefonnummer = $fehlerAnmerkung = "";

    //Werte säubern
    function test_input($data)
    {
        $data = trim($data);            //whitespace entfernen
        $data = stripslashes($data);    //entfernt alle Backslashes (\) aus Zeichenketten
        $data = htmlspecialchars($data); //Sonderzeichen werden umgewandelt
        return $data;
    }

    //Wenn das Formular abgesendet wurde und einen Wert enthählt, dann...:
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["Vorname"])) {
            $fehlerVorname = "Vorname wird benötigt";
        } else {
            $vorname = test_input($_POST["Vorname"]);
            // checkt ob Vorname nur Buchstaben und whitespace enthält
            if (!preg_match("/^[a-zA-ZäüöÄÜÖß\-]*$/", $vorname)) {
                $fehlerVorname = "Nur Buchstaben erlaubt";
            }
        }

        if (empty($_POST["Nachname"])) {
            $fehlerNachname = "Nachname wird benötigt";
        } else {
            $nachname = test_input($_POST["Nachname"]);
            // checkt ob Nachname nur Buchstaben und whitespace enthält
            if (!preg_match("/^[a-zA-ZäüöÄÜÖß\-]*$/", $nachname)) {
                $fehlerNachname = "Nur Buchstaben erlaubt";
            }
        }

        if (empty($_POST["Email"])) {
            $fehlerEmail = "Email wird benötigt";
        } else {
            $email = test_input($_POST["Email"]);
            // checkt ob E-mailaddresse korrekt ist
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fehlerEmail = "Ungültiges Format";
            }
        }
        if ($_POST["Telefonnummer"]) {
            $telefonnummer = test_input($_POST["Telefonnummer"]);
            //checkt ob Telefonnummer gültig ist
            if (!preg_match("/^[+0-9\s]*$/", $telefonnummer)) {
                $fehlerTelefonnummer = "Ungültige Telefonnumer";
            }
        }

        if (empty($_POST["Anmerkung"])) {
            $anmerkung = "Fülle das Textfeld aus";
        } else {
            $anmerkung = test_input($_POST["Anmerkung"]);
        }
    }

    //Nochmal überprüfen ob alle befüllt und richtig angegeben wurden. Erst dann darf in die DB eingefügt werden
    if (!empty($_POST['Vorname']) && (preg_match("/^[a-zA-ZäüöÄÜÖß\-]*$/", $vorname)) && !empty($_POST['Nachname']) && (preg_match("/^[a-zA-ZäüöÄÜÖß\-]*$/", $nachname)) && !empty($_POST['Email']) && (filter_var($email, FILTER_VALIDATE_EMAIL)) && (preg_match("/^[+0-9\s]*$/", $telefonnummer))) {
        mysqli_query($db, "INSERT INTO kontaktdaten (vorname, nachname, email, telefonnummer, nachricht) VALUES ('$vorname','$nachname','$email','$telefonnummer','$anmerkung')");


        //für personalisierte AlertBox (Kontaktformularbestaetigung)
        $sqlkontaktname = "SELECT nachname FROM kontaktdaten ORDER BY id DESC LIMIT 1";
        foreach ($pdo->query($sqlkontaktname) as $row) {
            $nachnameauskontaktdaten = $row['nachname'];
        }
        echo '<script type="text/javascript">alert("Ahoi!\nSehr geehrter Herr ' . $nachnameauskontaktdaten . ',\nIhre Nachricht wurde abgeschickt und Ihr Anliegen wird in Kürze bearbeitet.")</script>';
    ?><script language="javascript" type="text/javascript">
            document.location = "http://brunnenberger.de/";
        </script><?php
                }

                    ?>

    <main role="main">
        <section class="bg-img jumbotron text-center">
            <div class="container">
                <h1 class="jumbotron-heading">
                    Kontaktformular
                </h1>
                <p>
                    <br>
                    <br>
                </p>
            </div>
        </section>
        <div style="margin: auto; width: 70%;" class="album py-5">
            <p>Ihre Flaschenpost kommt garantiert bei uns an. Wir freuen uns von Ihnen zu hören.</p>
            <form class="was-validated" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <div class="form-row">
                    <div class="col-6 mb-3">
                        <label for="Vorname">Vorname </label>
                        <span class="error">* <?php echo $fehlerVorname; ?></span>
                        <input class="form-control is-invalid" name="Vorname" type="text" id="uid" value="<?php echo $vorname; ?>" pattern="^[a-zA-ZöüäÜÖÄß-]+$" required="'required'">
                    </div>

                    <div class="col-6 mb-3">
                        <label for="Nachname">Nachname </label>
                        <span class="error">* <?php echo $fehlerNachname; ?></span>
                        <input class="form-control is-invalid" name="Nachname" type="text" value="<?php echo $nachname; ?>" pattern="^[a-zA-ZöüäÜÖÄß-]+$" required="'required'">
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-6 mb-3">
                        <label for="Email">E-mail </label>
                        <span class="error">* <?php echo $fehlerEmail; ?></span>
                        <input class="form-control is-invalid" name="Email" type="text" value="<?php echo $email; ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required="'required'">
                        <small id="emailkleinschreiben" class="form-text text-muted">
                            nur Kleinschreibung
                        </small>
                    </div>

                    <div class="col-6 mb-3">
                        <label for="Telefonnummer">Telefonnr./ Mobilnr. </label>
                        <input class="form-control is-invalid" name="Telefonnummer" type="text" placeholder="z.B. 0171 1... / +49 171 1..." value="<?php echo $telefonnummer; ?>" pattern="^[+0-9\s]*$" optional>
                        <small id="telefonnummeroptional" class="form-text text-muted">
                            optional
                        </small>
                        <span class="error"> <?php echo $fehlerTelefonnummer; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-12 mb-3">
                        <label for="Anmerkung">Ihre Nachricht an uns:</label>
                        <span class="error">* </span>
                        <textarea class="form-control is-invalid" rows=3 name="Anmerkung" placeholder="Platz für Fragen oder Anmerkungen" required="'required'"></textarea>
                    </div>
                </div>
                <!-- <span class="error">* Erforderliches Feld.</span> -->
                <p><span class="error">* Pflichtangaben</span></p>
                <button class="btn btn-primary my-2" name="save" type="submit">Senden</button>
            </form>
        </div>
    </main>

    <?php
    include("../includes/footer.php");
    ?>