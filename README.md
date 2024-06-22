# PHOTO

Ce projet est un modèle de blog photo amateur.

## BESOINS

### Visiteurs

-   Les photos doivent être affichées sous forme de grilles et dans une lightbox.
-   Les informations concernant la photo doivent contenir leur date et leur lieu de prise de vue.
-   Les photos doivent être organisées.
-   Les photos doivent pouvoir être filtrées selon leurs différentes informations.
-   Les visiteurs non connectés ne doivent avoir accès qu'aux photos publiques.

### Visiteurs connectés

-   Les visiteurs connectés doivent avoir accès aux photos privées pour lesquelles ils ont une autorisation.
-   Les visiteurs connectés doivent pouvoir modifier leurs identifiants et leur mot de passe et supprimer leur profil.

### Administrateur

-   L'administrateur doit pouvoir créer, publier, modifier et supprimer les photos ainsi que les éventuels autres publications attachées.
-   L'administrateur doit pouvoir créer, modifier et supprimer les utilisateurs ainsi que leurs accès aux photos privées.

## CHOIX TECHNIQUES

### Langages de programmations

#### Back

-   PHP 8.3
-   MYSQL 8

### Frameworks

#### Front

-   Bootstrap

### Librairies

#### Back

-   intervention (modification des photos)
-   altorouter (routes)
-   valitron (validateur de formulaires)
-   symfony var-dumper (dumper)
-   whoops (affichage des erreurs)
-   fakerphp (hydratation de la base de données)

#### Front

-   Leaflet (affichage des lieux de prise de vue sur des cartes OpenStreetMap)
    -   Leaflet Search (recherche de coordonnées GPS par intitulé pour l'ajout de marker)
    -   Leaflet Merkercluster (regroupement des markers)

### Architecture

-   MVC

## FONCTIONNALITES

### Création des photos

Il est possible d'importer plusieurs photos en même temps dans un dossier public.
Lors de la création de photo :

1. les photos d'origines sont importées
2. les photos sont également importées au format WEBP et selon plusieurs tailles (XS, S, M, L, XL)
3. les informations suivantes sont renseignées automatiquement :
    - titre = nom du fichier
    - chemin = chemin d'accès au fichier d'origine
    - date de création = extraite des données exif de la photo
4. les photos sont enregistrées dans la base de données comme brouillons (état 'draft') et pourront être publiées (état 'published') par la suite pour être affichées dans le front office

### Lieux de prise de vue des photos

Un type de publications 'location' (Emplacement) permet de renseigner le lieu de prise de vue des photos.
Les emplacements sont hiérarchisés (Par exemple : Continent > Pays > Région > Département > Ville).
Les informations concernant les emplacements contiennent éventuellement des coordonnées GPS.

### Organisation des photos

Deux autres types de publications permettent d'organiser les photos:

-   'album' (Album)
-   'category' (Catégorie)

Les catégories sont hiérarchisées et une photo peut être attachée à plusieurs catégories.

### Accès public/privé aux photos

Les informations concernant les catégories contiennent la gestion de l'accès public ou privé. Ainsi :

-   Une photo est considérée comme privée si au moins une des catégories qui lui sont attachées est privée.
-   Un utilisateur connecté ne peut avoir accès à une photo privée que s'il est autorisé à voir toutes les catégories privées qui lui sont attachées.
-   Un album ou un emplacement ne sera pas affiché publiquement si toutes les photos qui leur sont attachées sont privées.
-   Un utilisateur connecté aura accès à un album ou un emplacement 'privé' si il a accès à au moins une photo privée qui leur est attachée.

### Modification des photos

Les informations suivantes peuvent être modifiées individuellement :

-   titre
-   description

Le fichier attaché à la publication peut également être remplacé.

Les informations suivantes peuvent être modifiées en masse :

-   date de création
-   emplacement
-   album
-   catégories (en masse, les catégories sont ajoutées mais ne remplacent pas celles qui sont déjà attachées)
-   état

### Modification des collections (autres publications)

Les collections peuvent également être publiées ou enregistrées comme brouillons.
Cet état peut également être modifié individuellement ou en masse.

### Supression des photos et des collections

L'ensemble des publications sont placées dans la corbeille (état 'trashed') avant de pouvoir être complètement supprimées de la base de données.

### Index des publications

En back office, les publications de chaque type sont affichées sous forme de tableau paginé, et sont filtrées par leur état (publiées, brouillons, corbeille).

### Utilisateurs

Les utilisateurs sont distingués par rôle (administrateur ou abboné).

#### Administrateur

L'administrateur a accès à l'ensemble de la gestion :

-   des publications
-   des autres utilisateurs
    -   création
    -   modification des identifiants, du role et des permissions accordées pour chaque catégorie privée
    -   suppression
-   de son profil
    -   modification de ses identifiants d'accès.

Cependant, seul un autre administrateur peut modifier son role et éventuellement ses permissions.

#### Abbonnés

Chaque abboné peut modifier ses propres identifiants d'accès.

### Front office

#### Photos

Les photos sont affichées sous forme de grilles paginées :

-   sur chaque page individuelle :
    -   d'album
    -   de catégorie
    -   d'emplacement
-   ainsi que sur une page regroupant toutes les photos.

Sur chacune de ces pages, les photos peuvent être filtrées par :

-   année
-   mois
-   emplacement (à l'exception de chaque page individuelle d'emplacement)
-   catégorie

Chaque photo peut être affichée dans une lightbox contenant les informations sur sa date et son lieu de création ainsi que l'album et les catégories qui lui sont attachées.
La lightbox est un carousel permettant de naviguer entre les photos affichées sur la page.

### Collections

#### Albums

L'index des albums affiche un grille paginée des albums.

### Publications hiérarchisées (Catégories & Emplacements)

L'index de chaque publication hiérarchisée affiche une grille des publications racines.

Sur chaque page individuelle d'une publication hiérarchisée, sont affichés :

-   un fil d'ariane vers les publications ascendantes
-   éventuellement, une grille des publications enfants
-   la grille des photos qui lui sont directement attachées ou attachées à ses descendants

### Emplacements

L'index des emplacements affiche une carte OpenStreetMap des emplacements sans enfants.

Sur chaque page individuelle d'un emplacement, une carte OpenStreetMap affiche :

-   si l'emplacement a des descendants, ses descendants sans enfants
-   sinon, l'emplacement et ses frères

## A FAIRE

### Back office

### Tableau de bord

[] Afficher une grille des catégories, des albums et des emplacements privés autorisés

### Collections de photos

[] Attacher les photos directement depuis le formulaire de modification d'une collection

### Utilisateur

[] Forcer l'utilisation d'un mot de passe fort
[] Permettre aux abonnés de demander la suppression de leur profil
[] Forcer l'existence d'au moins un administrateur
[] Restreindre la possibilité à l'administrateur de supprimer son propre profil

### Front office

[] Remplacer les grilles de publications hiérarchisées par des carousels

#### Photos

[] Justifier les grilles de photos
[] Remplacer la pagination des photos par un bouton 'charger plus' en ajax

#### Filtres

[] Filtrer les albums
[] Filtres ajax
[] Permettre la selection de plusieurs catégories lors du filtrage
[] Restreindre le choix des années et des mois

#### Collections

[] Thumbnails de même ratio sur les cartes et thumbnails en arrière plan du titre

### Sécurité

[] Sécuriser l'accès au dossier d'images

### Performance

[] Mise en cache
[] Chargement des photos diféré
[] Minification

### Référencement

[] Référencement
