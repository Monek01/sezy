# SEZY — Plateforme E-commerce Multi-Catégories

Stack : **Laravel 11** + **Inertia.js** + **Vue 3** + **MySQL** + **Tailwind CSS**

Implémentation du MVP V1 défini dans le cahier des charges SEZY v2.0 :
catalogue, panier, tunnel d'achat 3 étapes, paiement Mobile Money
(MTN, Moov, Wave) + carte, back-office (catalogue, commandes, clients).

---

## 1. Prérequis

- PHP **8.2+** avec extensions : `mbstring`, `xml`, `curl`, `pdo_mysql`, `zip`, `bcmath`, `gd` (ou `imagick`)
- Composer 2.x
- Node.js **18+** et npm
- MySQL **8.0+** (ou MariaDB 10.6+)
- Un serveur web (le serveur de dev `artisan serve` suffit en local)

## 2. Installation

```bash
cd SEZY

# 1. Dépendances PHP
composer install

# 2. Dépendances JS
npm install

# 3. Variables d'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
#    DB_DATABASE=sezy / DB_USERNAME=... / DB_PASSWORD=...
# Créer la base si besoin :
mysql -u root -p -e "CREATE DATABASE sezy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Migrations + données de démonstration
php artisan migrate --seed

# 6. Lien symbolique stockage (images produits)
php artisan storage:link

# 7. Build des assets front (dev)
npm run dev
# … ou pour la prod :
npm run build

# 8. Lancer le serveur Laravel (dans un autre terminal si npm run dev tourne)
php artisan serve
```

L'application est accessible sur **http://localhost:8000**.

### Comptes de démonstration (créés par le seeder)

| Rôle          | Email             | Mot de passe |
|---------------|-------------------|--------------|
| Super Admin   | admin@sezy.bj     | password     |
| Client démo   | client@sezy.bj    | password     |

Back-office accessible sur `/admin` une fois connecté avec le compte admin.

---

## 3. Paiements Mobile Money — mode mock vs production

Le système de paiement est **abstrait derrière une interface commune**
(`App\Services\Payment\PaymentGatewayInterface`), avec un driver par
opérateur : `MtnMomoGateway`, `MoovMoneyGateway`, `WaveGateway`,
`PayDunyaCardGateway` (carte bancaire via l'agrégateur PayDunya).

**Par défaut (`PAYMENT_MODE=mock` dans `.env`)** : aucun appel réseau
n'est fait. Le paiement passe automatiquement de `pending` à
`succeeded` après quelques secondes (configurable via
`PAYMENT_MOCK_DELAY_SECONDS`), ce qui permet de tester tout le tunnel
d'achat de bout en bout sans compte marchand.

**Pour passer en production** dès réception des comptes marchands :

1. Renseigner les clés API dans `.env` (voir section dédiée du fichier,
   une variable par opérateur : `MTN_MOMO_*`, `MOOV_MONEY_*`,
   `WAVE_*`, `PAYDUNYA_*`).
2. Passer `PAYMENT_MODE=live`.
3. Chaque driver dans `app/Services/Payment/` contient déjà
   l'implémentation de l'appel réel à l'API (sandbox + production) et
   un lien vers la documentation officielle de l'opérateur en
   commentaire. Vérifier les endpoints (notamment MTN, dont l'URL
   diffère entre sandbox et production) avant la mise en production.

Aucune information bancaire n'est jamais stockée en base (conforme §7
du cahier des charges) : seules les références de transaction et leur
statut sont conservées (table `payments`).

---

## 4. Authentification Google (OAuth)

Le login Google utilise Laravel Socialite. Pour l'activer :

1. Créer un projet sur [Google Cloud Console](https://console.cloud.google.com),
   activer "Google+ API" / "OAuth consent screen".
2. Créer des identifiants OAuth 2.0, type "Application Web".
3. Ajouter `http://localhost:8000/auth/google/callback` (puis l'URL de
   production) comme URI de redirection autorisée.
4. Renseigner `GOOGLE_CLIENT_ID` et `GOOGLE_CLIENT_SECRET` dans `.env`.

Tant que ces clés ne sont pas renseignées, le bouton "Continuer avec
Google" redirigera vers une erreur Socialite — le formulaire email/mot
de passe reste pleinement fonctionnel en attendant.

---

## 5. Structure du projet

```
app/
  Http/Controllers/        Contrôleurs boutique (catalogue, panier, checkout, profil)
  Http/Controllers/Admin/  Contrôleurs back-office (catégories, produits, commandes, clients, équipe)
  Http/Controllers/Auth/   Authentification (login, register, Google OAuth, reset password)
  Http/Middleware/         HandleInertiaRequests (props partagées), EnsureUserIsAdmin (rôles)
  Models/                  Modèles Eloquent (Product, Order, Cart, User, Payment, Coupon...)
  Services/                CartService, CheckoutService (logique métier)
  Services/Payment/        Passerelles de paiement (interface + 4 drivers + manager)

database/
  migrations/               Schéma complet MySQL (utf8mb4, clés étrangères, index)
  seeders/                  Données de démonstration (catégories, produits, codes promo)

resources/
  js/Pages/                 Pages Inertia (boutique + Admin/*)
  js/Layouts/                ShopLayout.vue (mobile-first, bottom nav) / AdminLayout.vue
  js/Components/             ProductCard, ProductGrid, FormInput
  views/pdf/                 Templates facture & bon de préparation (DomPDF)

routes/
  web.php                   Routes boutique + auth
  admin.php                 Routes back-office (préfixe /admin, middleware admin)
```

---

## 6. Couverture fonctionnelle (MVP — Phase 1 du cahier des charges)

✅ Catalogue : catégories illimitées, produits, variantes (couleur/taille），
   recherche, filtres, tri
✅ Fiches produit : galerie, variantes, avis (structure prête, modération
   à activer en Phase 2)
✅ Panier persistant (invité + connecté avec fusion à la connexion)
✅ Tunnel d'achat 3 étapes : Livraison → Paiement → Confirmation
✅ Paiement Mobile Money (MTN, Moov, Wave) + carte — mock prêt à
   brancher
✅ Guest checkout avec proposition de compte
✅ Back-office : catégories (drag-and-drop position), produits (CRUD +
   import/export CSV), commandes (workflow + factures PDF), clients
   (blocage, points fidélité), codes promo, gestion d'équipe/rôles
✅ Dashboard admin avec KPIs temps réel et graphiques
✅ 2FA : colonnes prêtes en base (`two_factor_secret` etc.), à activer
   via un package dédié (ex: `pragmarx/google2fa-laravel`) si souhaité
   pour la mise en production
✅ Programme de fidélité SEZY Points (base posée : accumulation et
   utilisation au checkout)
✅ Programme de parrainage (code unique généré, lien en base)
✅ Click & Collect (points de retrait, sélection au checkout)

Champs de la roadmap Phase 2/3 (notifications SMS, avis avec
modération complète, dashboard analytics avancé, app mobile,
marketplace, IA recommandations) sont **hors du périmètre MVP validé**
mais le modèle de données (`reviews`, `wishlists`, `audit_logs`) est
déjà en place pour les accueillir sans refonte.

---

## 7. Tests

```bash
php artisan test
```

Un test Feature (`tests/Feature/CheckoutTest.php`) couvre le parcours
critique : ajout panier → tunnel d'achat → création de commande →
décrément du stock, ainsi que la protection des routes admin.

---

## 8. Prochaines étapes suggérées avant mise en production

1. Obtenir et configurer les comptes marchands Mobile Money (MTN,
   Moov, Wave) + PayDunya, basculer `PAYMENT_MODE=live`.
2. Configurer un fournisseur d'emails transactionnels (SendGrid /
   Mailgun) dans `.env`.
3. Activer le 2FA obligatoire pour les rôles admin (`requiresTwoFactor()`
   est déjà présent sur le modèle `User`).
4. Configurer le stockage des images sur S3 / Cloudflare R2 en
   production (`FILESYSTEM_DISK=s3`).
5. Lancer un audit de sécurité (scan OWASP) avant la recette finale,
   conformément au §12 du cahier des charges.
