eBrigade 5.3
Développée et distribuée par eBrigade Technologies
Copyright (c) 2004-2021 Nicolas MARCHE (eBrigade Technologies)

License GPL V2.0 ou plus récente, voir le fichier license.txt
Site web: https://ebrigade.app

==============================================
=   Documentation complète
==============================================

https://ebrigade.app/wp/document/

==============================================
=   release note 5.3 - juin 2021
==============================================
- Refonte graphique complète
- Nouveau menu latéral
- Amélioration graphiques sur les filtres dropdown, ils contiennent une fonction de recherche
- Pagination sur les gros tableaux, boutons améliorés
- Expiration des compétences, le warning avant expiration est configurable
- Nouvelle page Wizard pour l'installation initiale
- Possibilité de clôturer automatiquement les événements quelques jours avant
- Amélioration du sélecteur de photo de profil
- Nouvelle page de login
- Nouveaux tooltips (ils peuvent être activés dans les préférences utilisateurs)
- Gestion des préférences utilisateurs dans un nouveau menu (choix de la section préférée et de l'ordre d'affichage des sections dans les filtres)
- Upload de fichiers améliorés, possibilités de choisir plusieurs fichiers.
- Remplacement de la librairie PHPExcel par PHPStreadsheet
- Nouvelle page d'assignation des piquets sur les tableaux de garde
- Possibilité de remplissage automatique des piquets
- Nouveaux reportings
- Gestion d'une timezone
- Paramétrage de l'ordre des gardes et de la garde par défaut
- graphique ancienneté moyenne du personnel
- Ajout possible de pièces jointes sur les interventions
- Possibilité de rattacher des consommables à un lot de matériel
- Export Excel des événements: ajout stats
- Gardes: le tableau peut maintenant être généré pour certains jours seulement
- Extraction Excel des entreprises
- Possibilité d'upgrade en un click pour les futures patches
- Ajout page modules

==============================================
=   release note 5.2 - août 2020
==============================================
- Groupes Whatsapp configurables sur les sections, événements
- Liens visio conférences sur les événements
- Widget Récapitulatif des heures de formation pour les pompiers
- Possibilité de faire expirer les mots de passes après un certain nombre de jours pour forcer les utilisateurs à les changer
- Possibilité d'ajouter des documents sur les fiches victimes
- Reportings, la liste est maintenant stockée dans la base de données (table report_list)
- Audit des extractions de reportings
- Améliorations de la liste de l'habillement
- Amlioration de la sécurité des photos (noms de fichiers aléatoires)
- Historisation des changements de rôles dans l'organigramme
- Enregistrement de la section préférée utilisée dans les filtres d'affichages
- Possibilité de forcer les utilisateurs à mettre une photo (paramètre de configuration photo_obligatoire)
- Amélioration des Statistiques de transport de victimes, permettre de choisir un type de transport dans la configuration des statistiques globales.
- Options obligatoires à l'inscription, l'option de type liste déroulante pet maintenant être configurée pour être obligatoirement renseignée
- Possibilité d'extraire la liste des contacts d'un événement pour créer un groupe de diffusion gmail
- Nouveau reporting ajout de compétences
- Possibilité d'afficher un message spécifique à la première connexion, à définir dans le fichier specific_info.php
- Améliorations de sécurité diverses
- Icône avec la photo dans la barre de menu
- Possibilité de modifier les images du thème depuis le menu de configuration (nouvel onglet personnalisation)

==============================================
=   release note 5.1 - janvier 2020
==============================================
- Amélioration des fiches véhicule et matériel, affichage des infirmations par onglets
- Support SMSEagle pour envoyer des SMS (https://www.smseagle.eu)
- Sapeurs Pompiers Professionnels - ajout régime de travail (12h,24h,SHR)
- Les tableaux de garde mensuels sont maintenant possibles aussi pour les associations
- Notes de frais, nouvelle permission n°77 pour permettre de les créer au lieu de n°11 (saisir ses absences)
- Nouveau type salarié, service national universel
- Nouvelles cartes de France jVectorMap, en remplacement de franceMap
- Gestion des licenses des adhérents (numéro, date et expiration)
- Amélioration de la fiche personnel, au lieu d'être désactivés, les champs ne sont plus éditables du tout
- Possibilité de bloquer les changements sur les fiches personnel (paramètre de configuration block_personnel)
- Upgrade FPDF 1.8.2
- Amélioration de la page configuration avec boutons bootstrap-toggle
- La section choisie pour l'affichage des données est conservée après deconnexion
- Statistiques transport de victimes possible selon qui a transporté sur le paramétrage du type d'événement
- Lien Waze sur la fiche événement et personnel si l'adresse est renseignée et la géolocalisation activée
- Nouvelle page utilisateurs en ligne découplée du chat
- Pour les associations, il est possible de bloquer la création de certains types d'événements voire tous pour une plage de dates données
- courbe de charge d'activité - graphique nombre de participations par jour des bénévoles selon le type d'activité
- améliorations de sécurité diverses

==============================================
=   release note 5.0.3 - décembre 2019
==============================================
- correction du format des fichiers sql, Unix au lieu de Windows, corrige des erreurs d'installations.
- corrections bugs liés aux sauvegardes manuelles, et icones sur la page de reload

==============================================
=   release note 5.0.2 - décembre 2019
==============================================
- correction d'un bug de collation avec MySQL 5.7 lors de la création de la base, mauvais affichage des accents

==============================================
=   release note 5.0.1 - septembre 2019
==============================================
- pas de mise à jour de la base de données
- corrections de bugs mineurs, en particulier affichage ou upgrade depuis version 4.4 ou inférieur
- amélioration de la page planning, ajout de filtre par type d'événement et de tris par date, et détail des participations

==============================================
=   release note 5.0 - juillet 2019
==============================================
principales nouveautés
----------------------
- Ajout de graphiques avec chart.js (remplacement des anciens graphiques optionnels ChartDirector)
- Nouvelles icônes: Font Awesome upgrade to 4.7.0
- Améliorations graphiques: Bootstrap upgrade to 4.3.1
- Remplacement des popups datepickers, bootstrap-datepicker est maintenant utilisé
- Ajout de documents sur le matériel
- Ajout du lieu de stockage pour les consommables
- Le statut ancien devient possible pour les externes
- Gestion des homonymes, possibilité de fusionner les doublons
- Sur la recherche par nom, ajout d'un filtre par section
- Gestion des gardes pour un SDIS
- Rôles de l'organigramme, nouvelle propriété "Membre de n'importe quelle section"
- Statistiques sur les événements, le nombre possible augmente de 4 à illimité pour chaque type d'événements
- Ajout des identifiants des outils de communication (Skype, Whatsapp, Zello)
- Associations: possibilité de bloquer temporairement la création de certains types d'événements
- Véhicules: ajout de propriétés Attelage et Public Address
- Cartes Google Maps: nouvelle permission permettant de voir les cartes
- Reporting: Liste des changements de rôles (nouveaux élus)
- Améliorations de sécurités suite à penetration tests

==============================================
=   release note 4.5.1 - février 2019
==============================================
- Améliorations de sécurité diverses sans mise à jour de la base de données

==============================================
=   release note 4.5 - 2019
==============================================
principales nouveautés
----------------------
- Les widgets de la page d'accueil sont maintenant configurables
- Améliorations graphiques sur la page événement, modal responsive bootstrap remplace popup boxes 
- Améliorations de sécurité CSRF
- Nouvelle méthode d'encryption proposée pour les mots de passe, bcrypt
- Nouvelle API gratuite de géolocatisation OSM (data.gouv.fr) en complément de Google
- Améliorations sur les notes de frais (ajout d'une double validation, améliorations affichage, choix section)
- Améliorations de la fiche victime, nouvel onglet secours psychologiques
- Formations individuelles: ajout du détail du paiement
- Feuille garde SP, les piquets configurés sont imprimés
- L'application peut maintenant être utilisée par des organisations militaires

==============================================
=   release note 4.4 - 2018
==============================================
principales nouveautés
----------------------
- Nouveau calendrier
- Page d'accueil, nouveaux widgets:
    - Horaires de travail à valider
    - Notes de frais à valider ou rembourser
    - Statistiques manquantes sur des événements
- Grades
    - Ajout de tous les grades pompiers du SSSM et des icônes correspondantes
- Garde Sapeurs-Pompiers
    - Possibilité de créer des équipes et de les voir sur la carte (si l'adresse de la garde est renseignée et la géolocalisation activée)
    - Possibilité d'activer ou désactiver la fonctionnalité remplacements
    - Ajout des compétences requises sur les gardes
    - Remplissage automatique du tableau de garde avec les SPV disponibles ( nécessite de paramétrer les compétences requises)
    - Nouvel onglet piquets sur la feuille de garde, avec compétence requise pour chaque rôle dans un véhicule
    Documentation détaillée ici: https://ebrigade.app/wiki/tableau-de-garde/
- Fiche personnel
    - Amélioration graphique du premier onglet maintenant en 3 blocs, responsive
    - ajout mail de la personne à contacter d'urgence
- Evénements
    - Rapport sur un événement: 
        - possibilité de choisir les éléments à imprimer sur le PDF
        - amélioration graphiques sur les boutons
    - Ajout du téléphone de contact sur les événements
    - Ajout d’un document PDF listant les produits consommables utilisés 
    - Ajout des options d'inscriptions sur les événements
        Détail ici https://ebrigade.app/wiki/options/
- Associations
    - Ajout des identifiants radio sur les départements et antennes
    - Possibilité de créer un renfort pour chaque département (en plus de un par antenne)
    - Notification des responsables des renforts quand on valide l'événement principal
    - Ajout de conventions sur les formations
    - Inscription du personnel, possibilité de filtrer avec ou sans les sous-sections le personnel à inscrire
- Horaires du personnel salarié 
    - Ajout d'un champ commentaire pour chaque jour
    - Ajout d'une vue des horaires travaillés jour par jour en plus de la vue par semaine
- Notes de frais
   - Ajout d'un champ commentaire
   - Ajout d'un numéro comptable, par défaut année / mois / numéro incrémenté depuis le début du mois.
   - Permettre une double validation (par exemple Président et Trésorier)
   - Améliorations ergonomiques
   - Nouveau widget notes de frais à valider sur la page d'accueil
   - Envoi de la note de frais au format PDF lors de la demande de validation
   - Commentaire en cas de rejet
- SMS
   - support de la nouvelle API v4 de SMSGateway.me, le password doit être remplacé par le token
- Possibilité de scanner des QR Codes de victimes
    Détail ici https://ebrigade.app/wiki/centre-daccueil-des-victimes/


==============================================
=   release note 4.3 - octobre 2017
==============================================
principales nouveautés
----------------------
- Nouvelle page d'accueil avec des widgets dont prochaines participations et alertes
- Support d'une nouvelle plateforme de SMS gratuite SMSGateway.me
- Ajout heure d'arrivée à l'hôpital pour les victimes
- Fonctionnalité "événements cachés" pour le personnel ayant la permission n°9
- Ajout d'une propriété "Formation possible" sur les compétences
- Ajout des décorations collectives
- Notes de frais:
   - Possibilité de faire don à l'association
   - Warnings si les justificatifs n'ont pas été attachés
- Gardes Sapeurs-pompiers:
   - Support de régime de travail complexes par exemple 12-24/12-48, ou 5 équipes
   - possibilité d'avoir une équipe SPP de garde différente la nuit du jour
   - La configuration est maintenant faite sur le paramétrage de chaque type de garde
   - Feuille d'inscription des SP sur les gardes
         - Ajout possibilité de choisir directement jour et/ou nuit
         - Ajout filtre pour afficher ou pas les SPP
- Ajout Nationalité et autres champs sur la page des photos du personnel
- La date d'expiration est maintenant obligatoire et forcée sur les compétences expirables
- Nouvelles icones pour les types d'événements
- Amélioration des QR Codes: ils donnent maintenant un lien URL vers les infos de la fiche personnel
- Mise à jour Nusoap pour les webservices, compatible PHP7
- Evénement renforts: possibilité de créer un renfort par antenne au niveau des départements


==============================================
=   release note 4.2 - mai 2017
==============================================

principales nouveautés
----------------------
1 - Possibilité de géo localiser une personne (ou un numéro de téléphone) en lui envoyant un SMS
2 - Enregistrement Ajax sur la page événement pour les fonctions et équipes du personnel, kilométrage des véhicules, nombre de matériel et consommables
3 - Sapeurs-pompiers: fonction de remplacement du personnel sur une garde:
    - la demande de remplacement peut être créée par
    - il peut proposer un remplaçant
    - le remplaçant proposé peut accepter ou refuser
    - le responsable de garde refuse ou valide le remplacement ce qui met à jour le tableau automatiquement
    - un nouveau menu Gardes > Remplacements permet de lister et rechercher tous les remplacements
4 - Impression des diplômes, possibilité d'imprimer le numéro d'événement
5 - Ajout d'un deuxième prénom sur les fiches personnel
6 - Sur le trombinoscope, possibilité d'affichages supplémentaires (2ème prénom, date et lieu de naissance, compétences, section, fonction)
7 - Possibilité de choisir directement l'entreprise à l'engagement d'un externe sur une formation
8 - Possibilités d'engager une ou plusieurs équipes sur les interventions
9 - Support MySQL 5.7
10 - Support de la nouvelle API SMS clickatell, tout en conservant le support de l'ancienne
11 - Améliorations graphiques (boutons bootstrap)
12 - Améliorations de sécurité (Cross Frame scripting, http headers)
13 - Modification de l'éditeur d'événements, choix du type de formation
14 - Paramétrage spécifique de l'impression des diplômes possible pour chaque département
15 - Historiques des modifications sur les sections
16 - Ajout de la nationalité sur les fiches personnel
17 - Possibilité de voir/modifier l'équipe sur les renforts
18 - Ajout du détail victimes adultes/enfants dans les rapport/bulletins PDF générés sur les événements
19 - Possibilité de trier le personnel inscrit sur la page événement (par Nom, Renfort, Fonction, Equipe ou Statut)
20 - Nouvelle page d'inscription du personnel sur les gardes sapeurs-pompiers, avec des indicateurs et des warnings


==============================================
=   release note 4.1 - décembre 2016
==============================================

principales nouveautés
----------------------
1 - Améliorations graphiques (suppressions des popups, nouvelles confirmations d'inscriptions)
2 - Possibilité de choisir une image de fond sur l'écran de login
    - mettre l'image dans images/user-specific/splash.jpg
    - quelques exemples dans images (splash1.jpg, splash2.jpg, splash3.jpg)
3 - Possibilité d'avoir plusieurs responsables par événement
4 - Ajout heure et lieu de rendez-vous sur les événements
5 - Ajout d'un libellé court pour la civilité (M., Mme. ...)
6 - Possibilité de définir les besoins en véhicules et matériel sur les événements, impression d'un PDF (demande de personnel et de moyens).
7 - Pour les SPP, nouvel écran (calendrier) pour saisir les repos liés au régime de travail
8 - Possibilité de choisir un statut pour les bénévoles inscrits sur les DPS (engagé, dispo base, dispo domicile, repos)
9 - Tableau de garde SP: possibilité d'afficher ou masquer les horaires
10 - Modification des permissions, pour voir tout le personnel les événements et les véhicules, il faut maintenant avoir la permission n°40
11 - Disponibilité pour les SP: possibilité d'ajouter un commentaire associé aux disponibilités du mois
12 - Marquer NPAI les fiches des personnes pour lesquels l'adresse postale est périmée.
13 - Possibilité d'inscrire ou pas les chiens de recherche et véhicules associés à un participant en même temps que lui même
14 - Affichage des SPP de la section du jour sur le graphique du personnel disponible par jour
15 - Bilans annuels PDF (personnel, moyens, activité) pour les associations
16 - Support d'un nouveau fournisseur de SMS - SMS Mode
17 - Icone alerte dans la barre de menu si il y a des CP à valider
18 - Extraction Excel possible du tableau de garde SP
19 - Support PHP 7.1
20 - Possibilité de saisir une Google API key pour la géolocalisation
21 - Génération d'un QR Code contenant les informations de la fiche du bénévole


==============================================
=   release note 4.0 - mai 2016
==============================================

principales nouveautés
----------------------
1 - Nouveau design responsive basé sur bootstrap et font-awesome
2 - Support de PHP 7.0
3 - Notification par mail possible si un nouveau message est posté sur le chat
4 - Pour Noël, on peut activer des flocons de neige qui tombent sur la page
5 - Nouvelle permission pour voir le tableau de garde pompiers
6 - Paramétrage de l'ordre pour les sections de gardes pompiers

==============================================
=   release note 3.5 - décembre 2015
==============================================

principales nouveautés
----------------------
1 - Eléments de facture configurables
2 - Passeport du bénévole: c'est un document PDF qui peut être télécharge depuis la fiche personnel et récapitule les compétences et participations.
3 - Horaires du personnel salarié: nouveau champ pour saisir les heures travaillées hors pointage
4 - Comptes bancaires: amélioration de la fonction de saisie IBAN avec des contrôles de validité ajoutés, élimination des RIB
5 - Véhicules: choix d'icônes pouvant être associé à chaque type de véhicule
6 - Bulletin de renseignement quotidien pour les associations de protection civiles
7 - Chiens de recherche et autres animaux: choix d'un maître
8 - Possibilités de poster des messages sans limitation de durée d'affichage
9 - Nouvelle propriété "colonne de renfort" sur les types d'événements, permettant de rattacher les événements par numéros.
10 - Attestation fiscale au format PDF sur la fiche personnel pour les cotisations des adhérents (mode gestion des adhérents).
11 - Possibilité de marquer les interventions importantes (par défaut pour DCD et détresses vitales)
12 - Réorganisation de la page Configuration en 3 onglets (base, avancée, sécurité)
13 - Page événement, possibilité de filtrer le personnel par jour et de masquer les compétences pour une meilleure lisibilité
14 - Page garde Sapeurs pompiers, possibilité de filtrer le personnel du jour et de la nuit
15 - Centre d'accueil des victimes
16 - Possibilité de dupliquer les habilitations
17 - Possibilité de marquer une garde en anomalie en cas de problème de personnel, la ligne apparaît en rouge dans le tableau
18 - Possibilité d'ajouter un sujet aux messages envoyés par mail

bugs résolus
--------------------
- 95 bugs corrigés

==============================================
=   release note 3.4 - février 2015
==============================================

principales nouveautés
----------------------
1 - Sécurité: contrôle renforcé des permissions
2 - Possibilité de créer ou modifier les types d'événements, en définissant en particulier
  * nom du type d'événement et catégorie
  * icône
  * onglets à afficher
  * documents à générer
  * statistiques à renseigner
3 - Possibilité d'envoyer un mail quand un nouveau message d'information est posté
  * avec pièces jointes intégrées dans le mail
  * par mailing de masses (supporte plusieurs dizaines de milliers de destinataires)
  * envoi asynchrone via crontab
4 - Notification supplémentaire lorsque les disponibilités d'un utilisateur ont été saisies
5 - Support de 2 nouveaux fournisseurs de SMS: envoyersmspro.com et SMS Gateway Android 
  * SMS Gateway est gratuit et permet d'envoyer jusqu'à 100 SMS par heure, ou plus on configurant Android avec root.
  * Cette solution nécessite d'avoir:
    - un smartphone Android dédié 
    - un forfait SMS illimité
    - une box ADSL configurée pour accepter les connections http entrantes et les rediriger sur le smartphone
6 - Possibilité d'affecter le matériel à une équipe sur les événements
7 - Fonction Mains courantes
  * accès en lecture restreint aux personnes inscrites
  * accès en écriture limité aux personnes inscrites avec la fonction "Rédacteur"
8 - Nouveau menu de paramétrage
9 - Identification de la commune par le zipcode sur la page section, comme pour le personnel. Nécessite de charger le fichier sql/zipcode.sql
10 - Reporting activé pour les casernes SP.
11 - Notification aux personnels SP quand le tableau de garde est prêt.
12 - Icône de pays à côté des numéros de téléphone. 
13 - Nouvelle page Documents / téléchargements


==============================================
=   release note 3.3 - décembre 2014
==============================================

principales nouveautés
----------------------
1 - Sécurité accrue (protection upload et XSS)
2 - Changements graphiques (rounded corner tables, 12 themes de couleurs supplémentaires, logo eBrigade)
3 - Ajout de Web services (user, sections, formations), voir soap.php
4 - Notifications paramétrables par chaque utilisateur
5 - Hiérarchies de compétences
6 - Gestion des Jeunes Sapeurs-Pompiers (nouveaux grades spécifiques)
7 - Expiration paramétrable des sessions utilisateurs
8 - Stock minimum sur les consommables
9 - Gestion des fonctions pour les véhicules  engagés sur les événements
10 - Mode astreinte pour les Sapeurs-Pompiers de garde
11 - Ajout du responsable légal et de la date d'envoi sur les conventions
12 - Optimisation de la sauvegarde permettant une recharge plus rapide des backups
13 – Génération d’un PDF pour chaque intervention comme on avait déjà pour chaque victime
14 – Reçu d’adhésion
15 – Ajout d’un lien vers la documentation dans le mail de création de compte.
16 - Ajout du responsable légal et de la date d'envoi sur les conventions

bugs résolus
--------------------
- 43 bugs corrigés


=============================================
=   release note 3.2 - août 2014
==============================================

principales nouveautés
----------------------
1 - Base de données optionnelle des codes postaux (pour activer, charger sql/zipcode.sql)
2 - Note de frais départementale
3 - Comptes SMS par département
4 - Conditions d'utilisation à accepter
5 - Gestion de la dotation vestimentaire (habillement)
6 - Dates spécifiques sur la convention
7 - Support PHP 5.6: remplacement de mysql par mysqli
8 - Documentation wiki
9 - Upgrade FPDI
10 - Acomptes sur devis et factures

bugs résolus
--------------------
- 28 bugs corrigés

==============================================
=   release note 3.1 - février 2014
==============================================

principales nouveautés
----------------------
1 - Ajout d'icônes pour les grades Sapeurs Pompiers
2 - Nouveau tableau de gardes Sapeurs Pompier
   * chaque garde est un événement, avec toutes les possibilités associées
   * paramétrage amélioré des types de gardes, avec icônes spécifiques, horaires précis
   * bouton créer/ supprimer le tableau de garde du mois
   * affichage du tableau par mois ou par semaine
   * possibilité d'enregistrer les interventions et victimes sur les gardes
   * les fonctions (piquets) sont devenus facultatifs
   * icones d'erreur si une personne de garde est indisponible, dejà engagée ou non qualifiée pour sa fonction
   * le personnel SPP peut être automatiquement engagé sur le tableau au moment de la création
   * l'engagement du personnel SPV sur chaque garde est simplifié et plus rapide
3 - Disponibilités du personnel en 1, 2, 3 ou 4 tranches horaires. Par défaut, 2 tranches jour et nuit.
4 - Ajout pagination sur l'onglet cotisations de la fiche personnel
5 - Réorganisation des dossiers contenant les fichiers
   * Par défaut tout est maintenant stocké dans le répertoire user-data au lieu d'être à la racine du site
   * diplomes, documents,files, files_message, files_section, files_vehicule, save
6 - Possibilité d'enregistrer plusieurs tracés de couleurs sur la carte SITAC Google Maps.

bugs résolus
--------------------
- 42 bugs corrigés   


==============================================
=   release note 3.0 - décembre 2013
==============================================

principales nouveautés
----------------------
1 - Carte Google maps avec la situation tactique (SITAC)
2 - Fiche bilan secouriste / médical complète pour les victimes
3 - Historique détaillé de tous les changements réalisés sur une fiche personnel
4 - Gestion des virements vers les comptes bancaires des adhérents
5 - Mise à jour des types d'interventions
6 - Enregistrement des BIC / IBAN en plus des RIB
7 - Rejets de prélèvements en cours de régularisation (représentation du montant le mois suivant)
8 - Sécurité accrue:
  * Stockage des adresses IP dans l'audit des connexions
  * Obligation d'avoir un caractère spécial dans les mots de passe
  * Vérification javascript de la qualité des mots de passes
  * Encryption des mots de passe au choix MD5 ou PBKDF2 (plus sécurisé)
9 - Possibilité d'imprimer un PDF de la fiche intervention et de la fiche victime
10 - Localisation GPS des utilisateurs connectés
12 - Recherche du personnel par compétence sur les carets Google Maps
13 - Meilleure gestion des homonymes lors de la création d'une fiche personnel
14 - Gestion des produits consommables (paramétrage, stock, consommation sur les événements)
15 - Paramétrage des types de véhicules
16 - Nombreux nouveaux reportings
17 - Reorganisation du code javascript, Prototype supprimé, tout est en jQuery
18 - Caserne SPP : gestion du temps de travail en 24/48 ou 24/72

bugs résolus
--------------------
- 265 bugs corrigés 

==============================================
=   release note 2.9 - mai 2013
==============================================

principales nouveautés
----------------------
1 - Gestion des notes de frais pour le personnel
2 - Gestion des horaires du personnel salarié
3 - Gestion des remboursements (comme les cotisations)
4 - Gestion des types d'interventions sur la main courante
5 - Possibilité de créer des dossiers pour classer les documents
6 - Mode maintenancce de l'application (seul admin peut se connecter)
7 - Ajout du nombre de places stagiaires sur les formations
8 - Possibilité de créer des fiches pour les animaux
9 - Nouvelles statistiques sur les événements
10 - Audit des changements de permissions du personnel
11 - Utilisation de PHPExcel pour générer tous les documents Excel
12 - Modifications de l'affichage de l'organigramme (mode liste et mode organigramme)
13 - Ajout de la signature du président, qui apparaît sur les PDF générés.
14 - Possibilité de champs personnalisés sur la fiche personnel (insérer dans la table custom_fields)
15 - Test sur la taille des fichiers a uploader

bugs résolus
--------------------
- 115 bugs corrigés 


==============================================
=   release note 2.8 - août 2012
==============================================

principales nouveautés
----------------------
1 - Gestion des cotisations du personnel, avec paramétrage de la cotisation par défaut par département.
2 - Gestion des comptes bancaires du personnel (RIB) pour les cotisations par prélèvement
3 - Gestion de la main courante, des interventions et des victimes, avec statistiques générées
    * Un rapport PDF de main courante peut être imprimé pour être remis à l'organisateur ou à la préfecture
    * Attention: l'enregistrement du dossier médical est soumis à des contraintes CNIL
    http://www.cnil.fr/la-cnil/actualite/article/article/securiser-les-donnees-de-sante-dans-les-applications-en-reseau
    * C'est pourquoi un nouveau paramètre de configuration "store_confidential_data" a été ajouté, qui interdit par défaut de stocker l'identité complète des victimes
    * une dizaine de rapports donnent des statistiques sur les victimes (âge, nationalité, transports , type d'intervention ... )
    * les anciennes statistiques sur opérations de secours existent toujours mais sont automatiquement renseignées à partir des interventions saisies.
4 - Nouvelle possibilités pour formater les messages d'information ou consignes (couleurs, polices de caractères ...), grâce à tinymce
5 - Possibilité d'ajouter des documents sur les véhicules
6 - Nouvelles données sur la fiche personnel: en particulier civilité
7 - Factures individuelles sur les formations
8 - Possibilité de bloquer les modifications sur les événements du passé, au-delà d'un nombre de jour configurable
9 - Gestion des organisations de type syndicat
10 - Historisations supplémentaires d'actions réalisées
11 - Liste des événements: enregistrements des favoris à plusieurs niveaux différents de l'organigramme
12 - Carte Google maps du personnel et des événements
13 - duplication multiple des événements (par exemple tous les mercredis pendant 8 semaines)
14 - Utilisation de la classe PHPMailer pour l'envoi de mails.

bugs résolus
--------------------
- 61 bugs corrigés 

==============================================
=   release note 2.7 - janvier 2012
==============================================

principales nouveautés
----------------------
1 - Possibilité de changer le thème de couleurs
2 - Génération des conventions DPS (format PDF)
3 - Notifications de rappel la veille de l'événement
4 - Gestion des astreintes
   * Pour chaque rôle de l'organigramme, on peut définir si une gestion d'astreinte est associée
   * Nouvelle page 'Astreintes' où on peut définir un tableau d'astreintes, dates libres.
   * chaque matin un tâche cron change l'organigramme en fonction du tableau d'astreintes.
5 - Les fonctionnalités 3 et 4 nécessitent d'avoir accès à la crontab du serveur (Linux)
   * configuration du paramètre eBrigade cron_allowed = 1
   * les scripts shells astreintes_updates.sh et reminder.sh doivent être ajoutés dans la crontab
   * exemple: envoi d'une notification à 18h15 aux participants la veille de l'événement
     15 18 * * * /var/www/vhosts/mydomain.org/httpdocs/reminder.sh
6 - Nouvelle carte 'Alerte des bénévoles'
7 - Les mails de notifications sur les événements ou demandes de congés contiennent un lien URL vers la bonne page
8 - Possibilité d'ajouter des documents sur la fiche personnel.
9 - Formats de dates uniformisés JJ-MM-AAAA
10 _ Ajout graphiques ChartDirector
    * statistiques détaillées sur les DPS
    * Nombre de formations par an
11 - FPDF upgrade 1.7
12 - Ajout fonction de Recherche de documents par nom
13 - Affichage des anniversaires à souhaiter
14 - Fonction de recherche dans le texte des messages postés
15 - Possibilité de dupliquer du matériel
16 - Ajout planning du personnel
17 - iPhone: Envoi vcards par email


bugs résolus
--------------------
- 38 bugs corrigés



==============================================
=   release note 2.6 - juillet 2011
==============================================

principales nouveautés
----------------------
1 - Cartes de France montrant l'activité opérationnelle, utilisation France Map (v3.0). 
2 - Optimisation des performances de l'application, stockage des permissions dans la session
3 - Pagination
4 - Découpage des évènements à jours multiples
  Ceci permet notamment de créer un événement et de le découper en plusieurs parties. ( Formation, DPS, Réunion etc....) 
  Il est possible d' inscrire le personnel, en partie ou en totalité sur les parties.('Petite horloge' de couleur modifiable)
  Pour chaque bénévole il est possible de personnaliser les horaires. 
  Les documents générés y compris la facturation et les reports prennent en compte le découpage. 
5 - Propriété 'DPS inter-associatif :' 'Coche' à activer dans la page de création du DPS ainsi que 
6 - Deux possibilités supplémentaires de duplications d'événements (personnel seulement, et matériel/véhicules seulement)
7 - Lien Google Maps sur les événements et les fiches personnel
8 - Date d'expiration des compétences
  - sur l'onglet diplômes de la page événement formation, ajout de dates d'expirations pour chaque personne 
  - ceci permet de mettre des dates de validité différentes selon les participants 
  - ces dates sont affichées sur l'attestation de formation 
  - la validité de la compétence est prolongée si la nouvelle date est postérieure à la date de fin de validité actuelle
9 - Evénement public et flux RSS
  Il est possible de cocher un événement et de le rendre 'public'. 
  Cette case a cocher permet d'alimenter un flux rss auquel il est possible de s'abonner avec un lecteur de flux rss, 
  mais aussi que chaque webmestre peut récupérer et intégrer dans son site internet. 
10 - Historique des actions réalisées
  - pour les personnes habilitées, une nouvelle icône apparait à droite en forme de loupe sur les fiches du personnel 
  - en cliquant sur l'icône, on ouvre la page historique pour la personne concernée 
  - On peut ainsi savoir qui a fait les modifications suivantes et quand: 
   . Ajout d'une fiche personnel 
   · Modification de fiche personnel 
   · Suppression d'une fiche personnel 
   · Modification de mot de passe 
   · Regénération de mot de passe 
   · Changement de section 
   · Changement de position 
   · Inscription 
   · Désinscription 
   · Commentaire sur inscription 
   · Modification Fonction 
   · Ajout compétence 
   · Modification compétence 
   · Suppression compétence
   . Modification de disponibilités
11 - Lien Skype
12 - Km véhicules personnels 
13 - Définition des compétences demandées sur l'événement.
  Sur la page événement, on peut maintenant définir le nombre de personnes demandées au global et pour chaque compétence. 
  Et ceci pour chaque partie de l'événement. Il est possible de modifier les informations en cliquant sur l’icône blanche en fin de ligne.
  Lorsqu'il y a assez de personnes inscrites, alors l'info apparait en vert. Sinon en rouge.
14 - Gestion des équipes
  On peut maintenant définir sur chaque évènement (ou renfort) des équipes, puis affecter le personnel inscrit sur ces équipes. 
  En cliquant sur l'onglet équipes de la page information d'un évènement, une nouvelle fenêtre s'ouvre qui permet d'ajouter, modifier ou supprimer des équipes. 
  Le nombre indique combien de personnes ont été affectées à chaque équipe. 
  Puis sur l'onglet personnel on peut maintenant affecter le personnel à une des équipes définies. 
  Vous pouvez utiliser cette fonctionnalité seulement sur l'évènement principal ou renfort ou sont inscrits les personnels.
15 - Lot de matériel
  Cette fonctionnalité permet, avec votre matériel existant, de l'associer dans un lot unique, lui même pouvant être associé à un véhicule, 
  et enfin l'ensemble étant engagé sur l'événement : soit en engageant le lot, soit en engageant le véhicule

bugs résolus
--------------------
- 65 bugs corrigés 


==============================================
=   release note 2.5 - décembre 2010
==============================================

principales nouveautés
----------------------
1 - gestion d'une présence partielle du personnel sur les événements
2 - impression des diplômes et duplicata de diplômes
3 - impression des attestations de formations
4 - impression des ordres de mission
5 - Messagerie instantanée (chat) + liste des utilisateurs connectés
6 - Support IPhone / IPad amélioré
7 - gestion du personnel externe (qui peut suivre des formations de secourisme)
8 - gestion des entreprise clientes
9 - attribution de fonctions au personnel sur les événements et paramétrage des fonctions possibles
10 - traçabilité accrue des actions importantes des utilisateurs
11 - performances améliorées sur les pages événement, sections
12 - véhicule et matériel affectés à une personne
13 - nombreuses statistiques dans les graphiques Chartdirector
14 - certaines compétences peuvent être modifiables par chaque utilisateur (exemple vaccinations)
15 - ajout de description sur les fonctionnalités
16 - Nouveau détecteur de navigateurs et OS
17 - gestion des absences possible par heures
18 - duplication des événements (simple ou complète avec le personnel, les véhicules et le matériel)
19 - choix de type de contrat et horaire pour le personnel salarié
20 - participation du personnel salarié en tant que bénévole ou salarié sur les événements
21 - gestion sécurisée des documents avec catégories et permissions d'accès
22 - possibilité de bloquer la saisie des disponibilités pour un mois donné
23 - catégories de messages (normal, informatique, urgent)
24 - gestion des photos d'identité, fonction de recadrage
25 - possibilité d'accès en lecture seule à l'application ebrigade
26 - gestion du mail secrétariat pour chaque section, qui reçoit toutes les notoifications
27 - choix des compétences devant être affichées sur les événements
28 - catégories d'événement (classification)
29 - Définition du type de DPS (PAPS, PE,ME,GE) Contrôle des agréments DPS
30 - amélioration de l'impression des badges.
31 - possibilité d'activer/désactiver presque toutes les fonctionnalités dans le menu Configuration
32 - cartes de france interactive pour localiser le personnel et l'activité en cours
33 - Nouvelle documentation en ligne

bugs résolus
--------------------
- 192 bugs corrigés 


==============================================
=   release note 2.4 - décembre 2009
==============================================

principales nouveautes
----------------------
1- changements graphiques divers, remplacement d'icones et utilisation d'onglets sur les pages personnel, evenement et section.
2- nouvelle fonction de recherche (par nom, par ville, par tel, par competences, par habilitation)
3- gestion du materiel 
 * configurer les types de materiel (parametrage / type de materiel)
 * saisir le materiel dans l'inventaire (inventaire / materiel)
 * ajouter du materiel sur les evenements
4 - comptabilite / facturation
 * cliquer sur l'icone en forme de calculatrice sur la page de chaque evenement pour acceder à ces fonctions
 * edition des devis et factures en format pdf
5 - Gestion des badges
6 - Ajout des membres dans les contacts Windows (cliquer sur l'icone carte de visite sur la fiche personnel)
7 - Export du personnel vers fichier CSV
8 - Export de la fiche evenement sous Excel
9 - Gestion des formations du personnel
10 - Evenements renforts
11 - Export des calendriers au format ical (clique sur l'icone calendrier de la page d'un evenement)
12 - Organigramme de la section modifie avec de nouveaux roles
13 - ajout information sur le sexe masculin/feminin des membres
14 - Photos d'identite
15 - securite accrue (contre injection sql, meilleure confidentialite des informations techniques du systeme)
16 - Gestion des agréments de sécurité civile
17 - Gestion des competences dans le cadre des casernes de sapeurs pompiers
18 - Gestion des roles dans l'organigramme, avec des permissions associees

changements mineurs
------------------
- plus de distinction prioritaire/non prioritaire pour le personnel inscrit a un evenement, mais couleur specifique pour les externes.
- affichage de la documentation a partir du menu aide, date des documents
- support de PHP 5.3.0

principaux bugs resolus
--------------------
- permissions parfois incorrectes sur la gestion des evenements

==============================================
=   release note 2.3 - décembre 2008
==============================================

principales nouveautes
----------------------
1 - gestion de plusieurs niveaux hierarchiques de sections dans la meme base de donnees. 
   * exemple: plusieurs centres de secours, plusieurs ADPC.
   * L'application est maintenant utilisable pour un nombre de membres tres important.
   * Gestion d'un organigramme de sections (exemple: national, zonal, regional, departemental, local)
   * les droits d'un utilisateurs sur l'application sont maintenant restreints a sa section (+ ses sous-sections)
   * exemple: un utilisateur ayant le droit de modifier le personnel peut le faire pour le personnel de sa section
    et de ses sous-sections mais pas des autres.
   * exemple 2: un utilisateur ayant le droit 'gestion des vehicules' peut ajouter nu vehicule dans sa section mais 
    pas dans une section d'un niveau superieur.
   * un evenement est visible dans le calendrier par les membres de la section organisatrice et ceux des sous-sections
   * la fonctionnalite "evenement exterieur" a ete ajoutee et permet a celui qui est authorise de creer 
    des evenements en dehors de sa section.
   * un message cree par un agent est visible par les membres de sa section et de ses sous-sections
   * les messages sont tries en fonction des destinataires

2 - l'identifiant ou matricule peut etre maintenant une combinaison de chiffres et lettres au lieu de numerique seulement.

3 - chaque utilisateur peut soi-meme modifier son identifiant (menu Session / Mes Infos).

4 - un responsable peut etre designe pour chaque evenement, il a alors tous les droits sur l'evenement.

5 - securite: les informations personnelles ne sont visibles que par les chefs de sections
    ou par les personnes habilitees a modifier le personnel.

6 - notion de cadre de permanence pour chaque section. Il recoit des notifications pour son secteur. 
    Des droits particuliers et temporaires peuvent etre automatiquement ajoutes.

7 - ajout d'une interface de configuration de la connexion a la base de donnees

8 - possibilite de definir des dates d'expiration des competences et un audit des changements de competences.

9 - fonctionnalite 'Securite locale' permettant de changer les groupes des utilisateurs (sauf admin) et de changer les mots de passe pour sa section.

10 - installation simplifiee: chargement automatique du schema de reference.

11 - upgrade automatique de la base de donnees.

12 - fonction mot de passe perdu, avec regeneration et envoi d'un mail.

13 - securite accrue configurable (longueur et qualite des mots de passes, lock apres X erreurs)

14 - Reporting et statistiques

14 - Gestion des heures de participation effective.
Si un evenement se deroule sur plusieurs jours, il faut pouvoir indiquer le nombre d'heures effectives de presence / activite.
La fiche evenement permet de definir la valeur par defaut pour l'evenement.
(ex : la formation PSC1 est proposee du Samedi 08h00 au Dimanche 12h00, mais le temps reel de formation et de 10 heures.)
La fiche participation permet de modifier le temps de presence des participants

changements mineurs
------------------
- ajout d'information (section et groupe) sur la page 'Mes Infos'
- stockage des fichiers attaches aux messages dans des sous repertoires specifiques.
- ajout d'un identifiant unique M_ID dans la table message.
- ne pas creer un repertoire pour les fichiers attaches des evenements tant qu'il n'y a pas de pieces jointes
- suppression d'une personne, les messages sont reaffectes a ADMIN au lieu d'etre perdus.
- ajout d'un lien vers chaque personne ou chaque vehicule dans la fiche d'un evenement.
- jusqu'a 99 soins / evacuations par DPS
- ajout des parametres de configuration auto_backup et auto_optimize
- sur la page configuration, grades et gardes sont incompatibles avec nbsections 0
- affichage de la liste des membres d'un groupe d'habilitations
- ajout de confirmations avant envoi de mais, d'alertes ou d'inscriptions a des evenements.
- affichage des destinataires d'un mail d'alerte.
- boutons d'envois d'email a partir des fiches personnel ou evenements
- affichage des prochaines inscriptions dans la fiche personnel
- selection de tous les jours ou toutes les nuits dansla page dispos
- calendrier: seules les inscriptions sont affichees
- calendrier: affichage du calendrier des autres utilisateurs possible

bugs resolus
------------
- bug sur PHP 5 (definition du type de fichier avec la balise <?php )
- bug MySQL 5 ( changements lies a la longueur de la chaîne encrytee pour le mot de passe)
- suppression des personnes avec un espace dans le nom ou le prenom
- format urlencoded dans les emails ( en particulier mauvais affichage des apostrophes)
- identifiant=0 ne doit pas etre autorise, ajout d'un contrôle sur la valeur identifiant ou matricule
- modification d'un evenement : perte des infos sur le nombre de soins et d'evacuations
- permissions 'modifier' et 'supprimer' inversees dans la gestion du personnel
- ajouts de checks sur les adresses email
- impossible de supprimer un vehicule si il y a des espaces dans l'immatruculation
- limitation du nombre de destinataires des emails pour eviter les erreurs sur la fonction mail limitee.
- support des apostrophes dans certains champs de saisie.

==============================================
=   release note 2.2
==============================================

liste des nouveautes
----------------------
1 - Envois de sms ( utilisation d'un compte au choix )
    * parametrage du numero de compte dans la page configuration
    * habilitation configurable pour "envoyer des SMS"
    * 3 fournisseurs de SMS sont utilisables ( clickatell est vivement recommande, le meilleur et le moins cher).

2 - Envoi d'alertes a une categorie de personnel selon qualification et / ou section d'appartenance.


changements mineurs
------------------
- affichage page personnel

bugs resolus
------------
- affichage evenement si habilite 17 mais pas 15
- ajout de contrôles sur l'identifiant (ou matricule)
- filtrage du texte des evenements ( suppression des balises HTML et des ")

==============================================
=   release note 2.1
==============================================

liste des nouveautes
----------------------
1 - ajout type d'evenement: Divers
2 - ajout du type d'evenement "Instructeur pour une formation"
    ces evenements ne sont visibles que par  le personnel 
    ayant une qualification de type 'PAE*'
3 - ajout d'un filtre par jour sur Vehicules/Engagements


==============================================
=   release note 2.0
==============================================

liste des nouveautes
----------------------

1 - evenements
-- possibilite pour l'administrateur d'ajouter et enlever du personnel
-- on ne peut plus se desinscrire
-- ajouter des vehicules sur des evenements
-- possibilite de cloturer les inscriptions ( ou d'ouvrir les inscriptions)
-- ajout d'informations (section organisatrice, numero) 
-- gestion de la riorite du personnel et de l'order d'inscription
-- filtre par section sur les evenements
-- ajout d'une date de fin dans le cas d'un evenement sur plusieurs jours (date de fin facultative)

2 - disponibilites
-- disponibilites par jour: ajout d'un filtre de tri par section et par qualification
-- disponibilites par jour: amelioration ergonomie du tableau
-- disponibilites jour/mois: split dispos mois et jour en 2 pages            
-- disponibilites du mois : ajout liste deroulante choix le jour ou la nuit.

3 - grille de depart par defaut modifiable. Cette grille conditionne les piquets dans "Garde du jour".
4 - configuration possible des sections (ajout, modif, suppression)
5 - parametrage possible des habilitations
6 - notifications par email ( lies aux evenements et aux demandes de CP)
7 - amelioration de l'affichage du calendrier
8 - remplacement des titres de la page d'accueil
9 - ajout de l'ecran engagement des vehicules
10 - purge glissante disponibilite et planning_garde
11 - lien dans mon calendrier vers garde du jour
12 - possibilite pour un agent de modifier son email et son numero de telephone
13 - masquage des grades parametrable
14 - Au lieu de poste de garde, on utilisera dans le cas ou il n'y a pas de garde le terme competence.
16 - configuration dans la base de donnees ( suppression du fichier config_param)


liste des principaux bugs resolus
---------------------------------
1 - crash si ajout de pieces jointes de plus de 5M 
2 - corrections des bugs firefox ( envoi email et graphiques)
3 - correction d'un bug dans le texte des emails pour les apostrophes ou apparaissait "\'" au lieu de '
4 - impossible de creer le premier evenement 

==============================================
=   release note 1.7
==============================================
1 - affichage des parametres de configuration
2 - configuration des equipes


==============================================
=   release note 1.6
==============================================
1 - possibilite de desactiver les menus vehicules, gardes.
2 - configuration des postes

==============================================
=   release note 1.5
==============================================
1 - histogrammes des disponibilites

==============================================
=   release note 1.4
==============================================
1 - possibilite de gestion sans sections ou en 3 sections (parametrable)
2 - sauvegarde automatique declenchee a la premiere connexion du jour
3 - correction de bug : page modification manuelle, lorsque la personne de garde supprime sa disponibilite.
4 - generation d'un script pour creer une nouvelle base (configuration par defaut)
5 - possibilite de decouper la journee en matin et apres midi ou pas (parametrable)

