CREATE TABLE facture(
   Fid VARCHAR(20),
   montant DECIMAL(10,2) NOT NULL,
   statut_paiement VARCHAR(50) NOT NULL,
   Pid VARCHAR(20) NOT NULL,
   Cid VARCHAR(20) NOT NULL,
   date_ DATE NOT NULL,
   PRIMARY KEY(Fid),
   UNIQUE(Pid),
   UNIQUE(Cid)
);

CREATE TABLE tache(
   Tid VARCHAR(20),
   Pid VARCHAR(20) NOT NULL,
   titre VARCHAR(50) NOT NULL,
   statut VARCHAR(50) NOT NULL,
   PRIMARY KEY(Tid),
   UNIQUE(Pid)
);

CREATE TABLE evenement(
   Eid VARCHAR(20),
   titre VARCHAR(50) NOT NULL,
   date_heure DATETIME NOT NULL,
   Pid VARCHAR(20) NOT NULL,
   PRIMARY KEY(Eid),
   UNIQUE(titre),
   UNIQUE(Pid)
);

CREATE TABLE projet(
   Pid VARCHAR(20),
   statut VARCHAR(50) NOT NULL,
   titre VARCHAR(100) NOT NULL,
   budget DECIMAL(10,2) NOT NULL,
   Cid VARCHAR(20) NOT NULL,
   Uid VARCHAR(20) NOT NULL,
   Fid VARCHAR(20) NOT NULL,
   Tid VARCHAR(20) NOT NULL,
   Eid VARCHAR(20) NOT NULL,
   PRIMARY KEY(Pid),
   UNIQUE(titre),
   UNIQUE(Cid),
   UNIQUE(Uid),
   FOREIGN KEY(Fid) REFERENCES facture(Fid),
   FOREIGN KEY(Tid) REFERENCES tache(Tid),
   FOREIGN KEY(Eid) REFERENCES evenement(Eid)
);

CREATE TABLE client(
   Cid VARCHAR(20),
   nom VARCHAR(35) NOT NULL,
   email VARCHAR(25) NOT NULL,
   tel VARCHAR(12) NOT NULL,
   Uid VARCHAR(20) NOT NULL,
   Fid VARCHAR(20) NOT NULL,
   Pid VARCHAR(20) NOT NULL,
   PRIMARY KEY(Cid),
   UNIQUE(email),
   UNIQUE(tel),
   UNIQUE(Uid),
   FOREIGN KEY(Fid) REFERENCES facture(Fid),
   FOREIGN KEY(Pid) REFERENCES projet(Pid)
);

CREATE TABLE user_(
   _Uid VARCHAR(20),
   nom VARCHAR(35) NOT NULL,
   email VARCHAR(25) NOT NULL,
   Mot_de_passe VARCHAR(20) NOT NULL,
   Cid VARCHAR(20) NOT NULL,
   Pid VARCHAR(20) NOT NULL,
   PRIMARY KEY(_Uid),
   UNIQUE(email),
   UNIQUE(Mot_de_passe),
   FOREIGN KEY(Cid) REFERENCES client(Cid),
   FOREIGN KEY(Pid) REFERENCES projet(Pid)
);
