# Ontime - Site E-commerce (v1)

Boutique en ligne pour Ontime (montres), avec back-office complet.
Stack: **HTML / CSS / JavaScript / PHP / MySQL**, pensee pour tourner sur **XAMPP**.

## 1. Installation (XAMPP)

1. Copiez le dossier `ontime-ecom` entier dans `C:\xampp\htdocs\` (Windows) ou `/Applications/XAMPP/htdocs/` (Mac).
2. Demarrez **Apache** et **MySQL** depuis le panneau de controle XAMPP.
3. Ouvrez `http://localhost/phpmyadmin`, creez une nouvelle base (ou laissez le script le faire) puis
   allez dans l'onglet **Importer** et importez le fichier `database/schema.sql`.
   -> Cela cree automatiquement la base `ontime_shop`, ses tables, et 3 produits d'exemple.
4. Ouvrez `config/config.php` et verifiez que `DB_USER` / `DB_PASS` correspondent a votre installation
   (par defaut XAMPP: `root` sans mot de passe -> deja configure).
5. Ouvrez `http://localhost/ontime-ecom/index.php` -> la boutique doit s'afficher avec la montre 3D dans le hero.

## 2. Acceder au tableau de bord admin

URL: `http://localhost/ontime-ecom/admin/login.php`
Identifiants par defaut:
- Utilisateur: `admin`
- Mot de passe: `password`

**Changez ce mot de passe des que possible.** Pour cela, generez un nouveau hash bcrypt (par ex. avec
`password_hash('votre-nouveau-mot-de-passe', PASSWORD_DEFAULT)` dans un script PHP temporaire) et
mettez a jour la colonne `password_hash` de la table `admin_users` dans phpMyAdmin.

Depuis le dashboard vous pouvez:
- **Analytique**: chiffre d'affaires, profit net (prix de vente - prix de revient), unites vendues,
  commandes en attente, graphique des 14 derniers jours, top produits.
- **Produits**: ajouter / modifier un produit (nom, description, prix, prix de revient, stock,
  categorie, image) - upload d'image direct depuis le formulaire.
- **Commandes**: voir chaque commande, changer son statut (en attente / confirmee / expediee / livree /
  annulee), ajouter le numero de suivi ZR Express.
- **Parametres**: nom de la boutique, reference des cles API ZR Express.

## 3. Activer les tarifs de livraison en temps reel (ZR Express)

Le formulaire de commande calcule les frais de livraison par wilaya via `api/shipping.php`.
Tant que vous n'avez pas de compte ZR Express actif, un **tableau de tarifs approximatifs** (modifiable
dans `includes/functions.php` -> `getFallbackShippingFee()`) est utilise automatiquement, donc le site
fonctionne des le premier jour.

Pour passer aux tarifs officiels en temps reel:
1. Creez un compte professionnel sur ZR Express / Procolis et recuperez votre **token** et votre **key**
   (ou **id** selon votre compte - verifiez le nommage exact dans leur documentation, car il varie
   parfois d'un compte a l'autre).
2. Ouvrez `config/config.php` et remplissez:
   ```php
   define('ZREXPRESS_TOKEN', 'votre_token');
   define('ZREXPRESS_KEY', 'votre_key');
   ```
3. `api/shipping.php` appellera automatiquement l'API ZR Express en premier, avec repli automatique sur
   le tableau local si l'API ne repond pas.

## 4. Structure du projet

```
ontime-ecom/
├── config/           connexion DB + cles API
├── database/         schema.sql a importer dans phpMyAdmin
├── includes/         fonctions PHP partagees, header/footer du site
├── assets/           css, js, watch3d.js (montre 3D Three.js)
├── uploads/           images produits uploadees depuis l'admin
├── api/              endpoint shipping.php (frais de livraison)
├── index.php, product.php, cart.php, checkout.php, order-confirmation.php
└── admin/            tableau de bord (login, dashboard, produits, commandes, parametres)
```

## 5. La montre 3D du hero

`assets/js/watch3d.js` genere une montre 3D avec Three.js (boitier, cadran, aiguilles, bracelet) -
entierement en JavaScript, aucun fichier 3D externe requis. Elle tourne automatiquement et se
laisse tourner librement a la souris / au doigt (OrbitControls). Pour utiliser une vraie photo de
cadran a la place du cadran uni, deposez une image dans `assets/img/watch-face.jpg` (le fichier
la charge automatiquement des qu'il existe).

## 6. Prochaines etapes possibles

Dites-moi simplement ce que vous voulez changer ou ajouter (ex: paiement en ligne, notifications
WhatsApp/SMS automatiques, page "mes commandes" cote client, export Excel des ventes, multi-langue
FR/AR, etc.) et je mettrai le code a jour.
