# 📚 SchoolApp — Documentation complète

## Sujet du projet

Conception et développement d'une application web de gestion scolaire
pour un établissement d'enseignement primaire (CP1 à CM2).
Projet réalisé dans le cadre du cours de Programmation Web et Framework.
Enseignant :
Lionel Marcus G. KABORET

## Membres

- OUEDRAOGO Tegawinde Cédric
- OUEDRAOGO Pouswende Ariane Léontia

> Application de gestion scolaire développée avec **Laravel 12** et **PHP 8.2**.  
> Gestion des classes, élèves, notes, paiements et bulletins PDF.

---

## 1. Présentation

SchoolApp est une application web de gestion scolaire conçue pour les écoles primaires.  
Elle permet à un **gestionnaire** d'administrer l'école et à des **enseignants** de saisir les notes de leurs élèves.

### Ce que l'application permet de faire

| Fonctionnalité                    | Gestionnaire | Enseignant  |
|---|---|---|
| Créer / supprimer des classes     |       ✅     |    ❌      |
| Créer / supprimer des matières    |       ✅     |    ❌      |
| Inscrire / supprimer des élèves   |       ✅     |    ❌      |
| Créer des comptes enseignants     |       ✅     |    ❌      |
| Enregistrer des paiements         |       ✅     |    ❌      |
| Voir le classement des élèves     |       ✅     |    ✅      |
| Saisir / modifier des notes       |       ❌     |    ✅      |
| Télécharger un bulletin PDF       |       ✅     |    ✅      |
| Télécharger un reçu de paiement   |       ✅     |    ❌      |

---

## 2. Technologies utilisées

| Technologie               | Version   | Rôle |
|---|---|---|
| PHP                       | 8.2+      | Langage backend |
| Laravel                   | 12        | Framework principal |
| Mysql                     | —         | Base de données (dev) |
| barryvdh/laravel-dompdf   | 3.1       | Génération de PDF |
| Google Fonts (Nunito)     | —         | Police de caractères |
| CSS personnalisé          | —         | Interface |

---

## 3. Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Mysql

### Étapes

```bash
# 1. Cloner le projet
git clone <url-du-repo> SchoolApp
cd SchoolApp

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Créer la base de données Mysql et modifier le fichier .env
(voir Configuration)

# 5. Lancer les migrations (crée toutes les tables)
php artisan migrate

# 6. Créer le lien symbolique pour les photos
php artisan storage:link

# 7. Inserer des données de test
php artisan db:seed
(voir 4. Données de test)

# 8. Lancer le serveur de développement
php artisan serve
```

L'application est accessible sur : **http://127.0.0.1:8000**

### Configuration de la base de données
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schoolapp
DB_USERNAME=root
DB_PASSWORD=ton_mot_de_passe
```

Puis lancer : `php artisan migrate`

---

## 4. Données de test

L'application est fournie avec un jeu de données de démonstration permettant de tester rapidement l'ensemble des fonctionnalités sans saisie manuelle.

### Insertion des données

Après avoir exécuté les migrations, lancez :

```bash
php artisan db:seed
```

Le seeder génère automatiquement :

### Gestionnaire

| Nom            | Email                                     | Mot de passe |
| -------------- | ----------------------------------------- | ------------ |
| Administrateur | [admin@gmail.com](mailto:admin@gmail.com) | Admin@1234   |

### Enseignants

| Nom           | Email                                               | Classe | Mot de passe |
| ------------- | --------------------------------------------------- | ------ | ------------ |
| Pierre KABORE | [pierre@schoolapp.com](mailto:pierre@schoolapp.com) | CP1    | Password@123 |
| Awa OUEDRAOGO | [awa@schoolapp.com](mailto:awa@schoolapp.com)       | CP2    | Password@123 |
| Moussa TRAORE | [moussa@schoolapp.com](mailto:moussa@schoolapp.com) | CE1    | Password@123 |
| Fatou ZONGO   | [fatou@schoolapp.com](mailto:fatou@schoolapp.com)   | CE2    | Password@123 |
| Issa SAWADOGO | [issa@schoolapp.com](mailto:issa@schoolapp.com)     | CM1    | Password@123 |
| Mariam BARRY  | [mariam@schoolapp.com](mailto:mariam@schoolapp.com) | CM2    | Password@123 |

Chaque enseignant est automatiquement affecté à une seule classe lors de l'exécution du seeder.

> ⚠️ **Important** : À la première connexion, vous serez obligatoirement redirigé vers une page pour changer ce mot de passe. Vous ne pouvez pas continuer sans le faire.

## 5. Structure du projet

```
SchoolApp/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Connexion, déconnexion
│   │   │   ├── ClasseController.php      # CRUD des classes + attribution matières/prof
│   │   │   ├── DashboardController.php   # Tableau de bord + statistiques
│   │   │   ├── EleveController.php       # CRUD des élèves
│   │   │   ├── EnseignantController.php  # CRUD des enseignants
│   │   │   ├── MatiereController.php     # CRUD des matières
│   │   │   ├── NoteController.php        # Saisie notes + bulletin PDF
│   │   │   └── PaiementController.php    # Versements + reçu PDF
│   │   │
│   │   └── Middleware/
│   │       ├── CheckFirstLogin.php       # Force le changement de mot de passe à la 1ère connexion
│   │       └── CheckEnseignant.php       # Bloque l'accès aux notes pour le gestionnaire
│   │
│   └── Models/
│       ├── User.php       # Utilisateurs (gestionnaire + enseignants)
│       ├── Classe.php     # Classes (CP1, CE1, CM2...)
│       ├── Eleve.php      # Élèves + calcul de moyenne
│       ├── Matiere.php    # Matières (Maths, Français...)
│       ├── Note.php       # Notes par élève/matière/trimestre
│       └── Paiement.php   # Versements de scolarité
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php          # Layout principal (sidebar + topbar)
│   │   ├── auth/
│   │   │   ├── login.blade.php        # Page de connexion
│   │   │   └── change_password.blade.php
│   │   ├── dashboard.blade.php        # Tableau de bord (classement 2 colonnes)
│   │   ├── classes/
│   │   │   └── index.blade.php        # Liste + modals ajouter/modifier/supprimer
│   │   ├── eleves/
│   │   │   └── index.blade.php        # Liste + modals ajouter/modifier/supprimer
│   │   ├── matieres/
│   │   │   └── index.blade.php        # Liste + modal ajouter/supprimer
│   │   ├── enseignants/
│   │   │   └── index.blade.php        # Liste + modal ajouter/supprimer
│   │   ├── notes/
│   │   │   ├── index.blade.php        # Classement + modals saisie/modification
│   │   │   ├── edit_fragment.blade.php # Formulaire chargé en AJAX dans le modal
│   │   │   └── bulletin_pdf.blade.php # Template HTML du bulletin PDF
│   │   └── paiements/
│   │       ├── index.blade.php        # Suivi paiements + modal versement
│   │       └── recu_pdf.blade.php     # Template HTML du reçu PDF
│   │
│   └── css/ (non utilisé — le CSS est dans public/)
│
├── public/
│   └── css/
│       └── app.css                    # TOUT le CSS de l'application
│
├── database/
│   ├── migrations/                    # Toutes les migrations
│   ├── seeders/                       # Les données de test
│   └── database.sqlite                # Fichier base de données SQLite
│
├── routes/
│   └── web.php                        # Toutes les routes de l'application
│
└── .env                               # Configuration de l'environnement
```

---

## 6. Base de données

### Schéma des tables

```
┌─────────────┐         ┌──────────────────┐         ┌─────────────┐
│    users    │         │  classe_matiere  │         │  matieres   │
│─────────────│         │  (table pivot)   │         │─────────────│
│ id          │         │──────────────────│         │ id          │
│ name        │         │ id               ┼         │             │
│             │         │ classe_id   ─────┼────────►│ nom         │
│ email       │         │ matiere_id       │         │ created_at  │
│ password    │         │ bareme (10|20)   │         │ deleted_at  │
│ role        │         │ user_id     ─────┼──┐      └─────────────┘
│ classe_id ──┼──┐      └──────────────────┘  │
│ is_first_   │  │               ▲             │      ┌─────────────┐
│   login     │  │               │             └─────►│    users    │
└─────────────┘  │      ┌────────┴────┐               │ (enseignant)│
                 │      │   classes   │               └─────────────┘
                 └─────►│─────────────│
                        │ id          │       ┌─────────────┐
                        │ nom         │       │    eleves   │
                        │ frais       │       │─────────────│
                        │             │       │ id          │
                        │ created_at  │◄──────│ classe_id   │
                        │ deleted_at  │       │ nom         │
                        └─────────────┘       │ prenom      │
                                              │ genre (M/F) │
                                              │ photo       │
                                              │ deleted_at  │
                                              └──────┬──────┘
                                                     │
                               ┌─────────────────────┤
                               │                     │
                        ┌──────▼──────┐    ┌─────────▼───┐
                        │    notes    │    │  paiements  │
                        │─────────────│    │─────────────│
                        │ eleve_id    │    │ eleve_id    │
                        │ matiere_id  │    │ montant     │
                        │ trimestre   │    │ created_at  │
                        │ valeurs     │    │ deleted_at  │
                        │ deleted_at  │    └─────────────┘
                        └─────────────┘
```

### Description des tables

**`users`** — Tous les comptes (gestionnaire + enseignants)
- `role` : `gestionnaire` ou `enseignant`
- `classe_id` : l'id de la classe assignée au prof (NULL pour le gestionnaire)
- `is_first_login` : force le changement de mot de passe à la première connexion

**`classes`** — Les classes de l'école (CP1, CE1, CM1...)
- `frais` : montant des frais de scolarité annuels en F CFA
- Soft delete : la suppression ne retire pas définitivement les données

**`matieres`** — Les matières enseignées (Mathématiques, Français...)
- Simple table avec juste le nom
- Liée aux classes via la table pivot `classe_matiere`

**`classe_matiere`** — Table pivot (relation many-to-many entre classes et matières)
- `bareme` : note maximale de la matière dans cette classe (**10 ou 20 uniquement**)
- `user_id` : l'enseignant qui gère la classe (même prof pour toutes les matières d'une classe)

**`eleves`** — Les élèves inscrits
- `classe_id` : la classe de l'élève
- `photo` : chemin vers la photo stockée dans `storage/app/public/photos_eleves/`

**`notes`** — Une ligne par élève + matière + trimestre
- `valeurs` : la note obtenue (ex: 14.5 sur 20, ou 7 sur 10)
- `trimestre` : 1, 2 ou 3
- Contrainte unique : un élève ne peut avoir qu'une seule note par matière par trimestre

**`paiements`** — Les versements de scolarité
- Chaque versement est une ligne séparée
- La somme de tous les versements d'un élève = total payé

---

## 7. Rôles et permissions

### Gestionnaire

Le gestionnaire est le compte administrateur principal. Il est **créé automatiquement** au premier lancement.

Il peut :
- Créer, modifier, supprimer des **classes** (avec matières et barèmes)
- Créer, supprimer des **matières**
- Inscrire, modifier, supprimer des **élèves**
- Créer, supprimer des **comptes enseignants**
- Enregistrer des **versements de scolarité**
- Voir le **tableau de bord** et le classement
- Télécharger les **bulletins** et **reçus PDF**

Il **ne peut pas** :
- Saisir ou modifier des notes (réservé aux enseignants)

### Enseignant

Un enseignant est créé par le gestionnaire et est assigné à **une seule classe**.

Il peut :
- Voir le classement de **toutes les classes**
- Saisir et modifier les notes des élèves de **sa classe uniquement**
- Télécharger les **bulletins PDF**

Il **ne peut pas** :
- Accéder aux fonctions d'administration (classes, élèves, paiements...)

### Middleware de protection

**`CheckFirstLogin`** : Redirige le gestionnaire vers la page de changement de mot de passe s'il n'a pas encore changé son mot de passe par défaut.

**`CheckEnseignant`** : Bloque l'accès à certaines routes si l'utilisateur n'est pas enseignant.

---

## 8. Fonctionnalités détaillées

### 8.1 Connexion et sécurité

Au premier lancement, un compte gestionnaire est créé automatiquement :
- Email : `admin@gmail.com`
- Mot de passe : `Admin@1234`

À la première connexion, le gestionnaire est **obligatoirement redirigé** vers une page de changement de mot de passe. Il ne peut accéder à aucune autre page tant que le mot de passe n'est pas changé.

### 8.2 Création d'une classe

Lors de la création d'une classe, on configure en une seule fois :

1. **Le nom** (ex: CP1, CE2, CM1...)
2. **Les frais de scolarité** annuels en F CFA
3. **L'enseignant responsable** — seuls les enseignants sans classe assignée apparaissent dans la liste. Une fois assigné, un enseignant n'est plus disponible pour d'autres classes.
4. **Les matières** — on coche chaque matière à inclure et on saisit son barème (10 ou 20)

Ces informations sont sauvegardées dans deux tables :
- La classe dans `classes`
- Les matières + barèmes dans `classe_matiere` (table pivot)
- L'enseignant : sa colonne `classe_id` dans `users` est mise à jour

### 8.3 Modification d'une classe

Le modal de modification recharge automatiquement :
- Les matières déjà cochées pour la classe (pré-cochées)
- Leurs barèmes actuels (pré-remplis)
- L'enseignant actuel (pré-sélectionné)

Toutes les modifications sont possibles : ajouter ou retirer des matières, changer les barèmes, changer l'enseignant.

### 8.4 Saisie des notes

**Accès** : Enseignants uniquement. Le gestionnaire voit le classement mais pas le bouton de saisie.

**Comment ça fonctionne :**

1. Le prof connecté a `classe_id = X` dans son compte
2. La liste des élèves ne montre que ceux dont `classe_id = X`
3. Les matières affichées sont uniquement celles configurées pour la classe X
4. Chaque champ de note affiche `/10` ou `/20` selon le barème de la matière

**Exemple :**
```
Classe CP1 : Maths /20 | Français /20 | Sport /10

→ Pour l'élève Paul SAWADOGO :
   Maths  : [14  ] /20
   Français : [16 ] /20
   Sport  : [8   ] /10
```

La note est sauvegardée via `updateOrCreate` : si une note existe déjà pour cet élève / matière / trimestre, elle est mise à jour. Sinon, elle est créée.

### 8.5 Suivi des paiements

- On enregistre des **versements** (avec possibilité de payer la totalité en une fois)
- Le reste à payer = Frais de la classe − Somme de tous les versements
- On ne peut pas verser plus que le reste dû (protection dans le controller)
- Une barre de progression visuelle indique le pourcentage payé
- Téléchargement d'un **reçu PDF** disponible pour chaque élève (Avec des détails de tout les versements effectués précedemment)

---

## 9. Calcul des moyennes

La moyenne est toujours ramenée **sur 10**, quelle que soit la note maximale de la matière.

### Formule

```
Pour chaque matière :
    note_sur_10 = (note_obtenue / bareme) × 10

Moyenne générale = somme(notes_sur_10) / nombre_de_matières
```

### Exemple concret

```
Élève : Paul SAWADOGO — Classe CP1 — Trimestre 1

Maths     : 14/20 → (14/20) × 10 = 7.00/10
Français  : 16/20 → (16/20) × 10 = 8.00/10
Sport     :  8/10 → (8/10)  × 10 = 8.00/10

Moyenne = (7.00 + 8.00 + 8.00) / 3 = 7.67/10 ✅ Admis (≥ 5/10)
```

### Seuil d'admission

- Moyenne **≥ 5/10** → Admis ✅
- Moyenne **< 5/10** → Non admis ❌

Ce calcul est fait dans la méthode `moyenneTrimestrielle()` du modèle `Eleve` :

```php
// app/Models/Eleve.php
public function moyenneTrimestrielle($trimestre = 1)
{
    // 1. Récupérer les notes du trimestre
    // 2. Pour chaque note, récupérer le barème de la matière dans la classe
    // 3. Ramener sur 10 : (valeurs / bareme) * 10
    // 4. Faire la moyenne
}
```

---

## 9. Génération PDF

L'application utilise le package **barryvdh/laravel-dompdf** pour générer deux types de PDF.

### Bulletin scolaire

**Route :** `GET /bulletin/{eleve}?trimestre={1|2|3}`  
**Fichier template :** `resources/views/notes/bulletin_pdf.blade.php`  
**Nom du fichier téléchargé :** `bulletin_NOM_PRENOM_T1.pdf`

Contenu du bulletin :
- En-tête avec le nom de l'école et le trimestre
- Informations de l'élève (nom, classe, genre, année scolaire)
- Tableau de toutes les notes avec : matière | note obtenue | barème | note/10 | mention
- Moyenne générale avec verdict Admis / Non admis et le rang

### Reçu de paiement

**Route :** `GET /paiements/{eleve}/recu`  
**Fichier template :** `resources/views/paiements/recu_pdf.blade.php`  
**Nom du fichier téléchargé :** `recu_NOM_PRENOM.pdf`

Contenu du reçu :
- Informations de l'élève et de sa classe
- Historique de tous les versements avec dates
- Total payé et reste à payer

---

## 11. Routes de l'application

```
GET    /                           → Redirection vers /login
GET    /login                      → Page de connexion
POST   /login                      → Traitement de la connexion
POST   /logout                     → Déconnexion
GET    /password/change            → Formulaire changement de mot de passe
POST   /password/change            → Enregistrement du nouveau mot de passe

GET    /dashboard                  → Tableau de bord

GET    /classes                    → Liste des classes
POST   /classes                    → Créer une classe
PUT    /classes/{id}               → Modifier une classe
DELETE /classes/{id}               → Supprimer une classe

GET    /matieres                   → Liste des matières
POST   /matieres                   → Créer une matière
DELETE /matieres/{id}              → Supprimer une matière

GET    /eleves                     → Liste des élèves
POST   /eleves                     → Inscrire un élève
GET    /eleves/{id}/edit           → Formulaire modification élève
PUT    /eleves/{id}                → Modifier un élève
DELETE /eleves/{id}                → Supprimer un élève

GET    /enseignants                → Liste des enseignants
POST   /enseignants                → Créer un enseignant
DELETE /enseignants/{id}           → Supprimer un enseignant

GET    /notes                      → Classement des élèves
GET    /notes/create               → Formulaire saisie de notes
POST   /notes                      → Enregistrer les notes
GET    /notes/{eleve}/edit/{trim}  → Formulaire modification
PUT    /notes/{eleve}/edit/{trim}  → Modifier les notes
GET    /notes/{eleve}/edit/{trim}/fragment  → Fragment AJAX pour le modal
GET    /bulletin/{eleve}           → Télécharger le bulletin PDF

GET    /paiements                  → Suivi des paiements
POST   /paiements                  → Enregistrer un versement
GET    /paiements/{eleve}/recu     → Télécharger le reçu PDF
```
