<?php
  
require('PersonnagesManager.php');
require('personnage.php');

  session_start();
  
  if (isset($_GET['deconnexion'])){
      session_destroy();
      header('Location: ../Mini_jeu_de_combat/index.php');
      exit();
  }

  if (isset($_SESSION['perso'])){
      $perso = $_SESSION['perso'];
  }
  
  $bdd = new PDO('mysql:host=localhost;dbname=Combat','root','');
  $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  
  $manager = new PersonnagesManager($bdd);
  
  if (isset($_POST['creer']) && isset($_POST['nom'])){
      $perso = new Personnage(['nom' => $_POST['nom']]);
      
      if (!$perso->nomValide()){
          $message = '<center><i><b>Le nom choisi est invalide.</center></i></b>';
          unset($perso);
      } elseif ($manager->exists($perso->nom())){
          $message = '<center><b><i>Le nom du personnage est déjà pris.</center></i></b>';
          unset($perso);
      } else {
          $manager->add($perso);
      }
      
  } elseif (isset($_POST['utiliser']) && isset($_POST['nom'])){
      if ($manager->exists($_POST['nom'])){
          $perso = $manager->get($_POST['nom']);
      } else {
          $message = '<center><i><b>Ce personnage n\'existe pas !</center></i></b>';
      }
      
  } elseif (isset($_GET['frapper'])){
      
      if (!isset($perso)){
          $message = 'Merci de créer un personnage ou de vous identifier.';
      } else {
          if (!$manager->exists((int) $_GET['frapper'])){
              $message = 'Le personnage que vous voulez frapper n\'existe pas!';
          } else {
              
              $persoAFrapper = $manager->get((int) $_GET['frapper']);
              $retour = $perso->frapper($persoAFrapper);
              
              switch($retour)
              {
                  case Personnage::CEST_MOI :
                      $message = 'Mais... pourquoi voulez-vous vous frapper ???';
                      break;
                  case Personnage::PERSONNAGE_FRAPPE :
                      $message = '<center>Le personnage a bien été frappé !</center>';
                      
                      $perso->gagnerExperience();
                      
                      $manager->update($perso);
                      $manager->update($persoAFrapper);
                      
                      break;
                  case Personnage::PERSONNAGE_TUE;
                      $message = 'Vous avez tué ce personnage !';
                      
                      $perso->gagnerExperience();
  
                      $manager->update($perso);
                      $manager->delete($persoAFrapper);
                      
                      break;
              }
          }
      }
  }
  
  ?>
  <!DOCTYPE html>
  <html>
      <head>
          <title>TP : Mini jeu de combat</title>
          <meta charset="utf-8" />
          <link rel="stylesheet" type="text/css" href="Personnages.css"/>
      </head>
      <body>
      
          <p> Nombre de personnages créés : <?= $manager->count() ?></p>
      <?php
          if (isset($message)){
              echo '<p>'. $message . '</p>';
          }
          
          if (isset($perso)){
          ?>
              <p><a href="?deconnexion=1">Déconnexion</a></p>
          
              <fieldset>
                  <legend><b>Mes informations</b></legend>
                  <p><center>
                      <b></i>Nom</b></i> : <?=  htmlspecialchars($perso->nom()) ?><br />
                      <b><i>Dégâts</b></i> : <?= $perso->degats() ?>
                      <b><i>Expérience</b></i> : <?= $perso->experience() ?>
                      <b><i>Niveau</b></i> : <?= $perso->niveau() ?>
                      <b><i>Nombre des coups</b></i> : <?= $perso->nbCoups() ?>
                      <b><i>Date de dernier coup</b></i> : <?= $perso->dateDernierCoup()->format('d/m/Y') ?>
                  </p></center>
              </fieldset>
              <fieldset>
                  <legend><b>Qui frapper?</b></legend>
                  <p>
                      <?php
                      
                      $persos = $manager->getList($perso->nom());  
                      if (empty($persos)) {
                          echo 'Personne à frapper!';
                      } else {
                          foreach($persos as $unPerso){
                              echo '<a href="?frapper='.$unPerso->id().'">'.htmlspecialchars($unPerso->nom()).'</a> (dégâts : '.$unPerso->degats().', expérience : '.$unPerso->experience().', niveau : '.$unPerso->niveau().', nombre des coups : '.$unPerso->nbCoups().', date de dernier coup : '.$unPerso->dateDernierCoup()->format('d/m/Y').')<br />';   
                          }
                      }
                      
                      ?>
                  </p>
              </fieldset>
              
          <?php
          } else { 
      ?>
              <form action="" method = "post">
              <link href="https://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet"> 
                  <p><center>
                      Nom : <input type="text" name="nom" maxlength="50" />
                      <input type="submit" value = "Créer ce personnage" name="creer" />
                      <input type="submit" value = "Utiliser ce personnage" name="utiliser" />
                  </p></center>
              </form>
      <?php
          }
      ?>
      </body>
  </html>
  <?php
  if (isset($perso)){
      $_SESSION['perso'] = $perso;
  }
  ?>