<?php

class AccueilController extends Controller {

    private $model;
    private $view;

    public function __construct($args) {
        parent::__construct($args);
        self::redirectIfConnected();
        include "model/Accueil.php";
        include "view/Accueil.php";
        $this->model = new AccueilModel();
        $this->view = new AccueilView();
    }

    public function display() {
        $this->view->setView("accueil.php");
    }

    public function inscription() {
        if($_POST["passwd"] != $_POST["passwdconf"]) {
            View::error(12);
        }
        if($this->model->userExists($_POST["id"], $_POST["mail"])) {
            View::error(13);
        }
        //le nom d'utilisateur est disponible
        if(!$this->model->registerNewUser($_POST["id"], $_POST["mail"],
                $_POST["passwd"] , $_POST["name"], $_POST["firstname"])) {
            View::error(14);
        }
        mail($_POST["mail"], "Confirmation d'inscription sur CVTheque", "Bonjour "
                . $_POST["id"] . ",\r\n\r\nVotre compte a bien été enregistré. Pour"
                . " confirmer votre inscription, Veuillez cliquer sur ce lien :\r\n"
                . "ici, futur lien\r\n\r\nSi vous n'êtes concerné par ce message,"
                . " vous pouvez l'ignorer. Nous vous prions de bien vouloir"
                . " nous excuser pour la gêne occasionnée.", "From: noreply@zankia.fr");
        $this->view->setView("inscription.php");
    }

    public function connexion() {
        if(isset($_POST["recover"])) {
            header("Location: retrouverMP/" . $_POST["id"]);
        }
        if(!($userAttributes = $this->model->getConnection($_POST["id"],
                $_POST["passwd"]))) {
            View::error(11);
        }
        //l'utilisateur est verifié
        $_SESSION["connected"] = true;

        foreach($userAttributes as $key => $att) {
            $_SESSION[$key] = $att;
        }
        header("Location: " . URL_ROOT_PATH . "Stream");
    }

    public function retrouverMP($name) {
        if(($result = $this->model->recoverPasswd($name[0])) === false) {
            View::error(15);
        }
        mail($result["email"], "Votre nouveau mot de passe sur CVTheque", "Bonjour "
                . $result["nickname"] . ",\r\n\r\nVoici votre nouveau mot de passe :\r\n"
                . $result["pass"] . "\r\n\r\nCe mot de passe doit rester"
                . " confidentiel, veuillez ne pas le montrer ni le communiquer.",
                "From: noreply@zankia.fr");
        $this->view->setView("retrouverMP.php");
    }
}
