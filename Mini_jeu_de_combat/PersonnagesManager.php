<?php
class PersonnagesManager
{
    private $bdd;
     
    public function __construct($bdd)
    {
        $this->setBdd($bdd);
    }
    //On ajoute un nouveau personnage
    public function add(Personnage $perso)
    {
        $req = $this->bdd->prepare('INSERT INTO personnages (nom) VALUES (:nom)');
        $req->bindValue(':nom', $perso->nom());
        $req->execute();
         
        $perso->hydrate([
            'id'=>$this->bdd->lastInsertId(),
            'degats' => 0,
            'experience' => 0,
            'niveau' => 1,
            'nbCoups' => 0,
            'dateDernierCoup' => '0000-00-00',
            ]);
    }
     
    public function count()
    {
        return $this->bdd->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }
     
    public function delete(Personnage $perso)
    {
        $this->bdd->exec('DELETE FROM personnages WHERE id = '.$perso->id());
    }
     
    public function exists($info)
    {
        if (is_int($info))
        {
            return (bool)$this->bdd->query('SELECT COUNT(*) FROM personnages WHERE id = '.$info)->fetchColumn();
        }
         
        $req = $this->bdd->prepare('SELECT COUNT(*) FROM personnages WHERE nom = :nom');

        $req -> execute([':nom' => $info]);
         
        return (bool) $req->fetchColumn();
    }
     
    public function get($info)
    {
        if (is_int($info))
        {
            $req = $this->bdd->query('SELECT id, nom, degats, experience, niveau, nbCoups, dateDernierCoup FROM personnages WHERE id = '.$info);
            $donnees = $req->fetch(PDO::FETCH_ASSOC);
        
            return new Personnage($donnees);
        }
         
        $req = $this ->bdd->prepare('SELECT id, nom, degats, experience, niveau, nbCoups, dateDernierCoup FROM personnages WHERE nom = :nom');
        $req->execute([':nom' => $info]);

        $donnees = $req->fetch(PDO::FETCH_ASSOC);
         
        return new Personnage($donnees);
    }
     
    public function getList($nom)
    {
        $persos = [];
 
        $req  =  $this->bdd->prepare('SELECT id, nom, degats, experience, niveau, nbCoups, dateDernierCoup FROM personnages WHERE nom <> :nom ORDER BY nom');
        $req->execute([':nom'=>$nom]);
 
        while ($donnees = $req->fetch(PDO::FETCH_ASSOC))
        {
            $persos[] = new Personnage($donnees);
        }
          return $persos;
        }

    public function update(Personnage $perso)
    {
        $req  =  $this->bdd->prepare('UPDATE personnages SET degats = :degats, experience = :experience, niveau = :niveau, nbCoups = :nbCoups, dateDernierCoup = :dateDernierCoup WHERE id = :id');
       
        $req->bindValue(':id', $perso->id(), PDO::PARAM_INT);
        $req->bindValue(':degats',$perso->degats(), PDO::PARAM_INT);
        $req->bindValue(':experience',$perso->experience(), PDO::PARAM_INT);
        $req->bindValue(':niveau',$perso->niveau(), PDO::PARAM_INT);
        $req->bindValue(':nbCoups',$perso->nbCoups(), PDO::PARAM_INT);
        $req->bindValue(':dateDernierCoup',$perso->dateDernierCoup()->format('Y-m-d'), PDO::PARAM_STR);
        $req->execute();
    }

    public function setBdd(PDO $bdd)
    {
        $this->bdd = $bdd;
    } 
}