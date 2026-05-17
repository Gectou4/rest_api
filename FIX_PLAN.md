# Plan de correction Mago Lint

Ce plan detaille les corrections a apporter au code PHP pour passer le lint Mago,
ordonnees par priorite et effort.

## Phase 1 : Corrections rapides (low effort, high impact)

### 1.1 `$_REQUEST` → `$_GET` / `$_POST`
**Fichiers** : `tests/ApiTest.php`, `src/App/Api.php` (deja fait)
**Action** : Remplacer `$_REQUEST['request']` par `$_GET['request'] ?? $_POST['request'] ?? ''`
**Statut** : ✅ Fait dans les tests

### 1.2 `isset()` → `array_key_exists()` ou comparaison explicite
**Fichiers** : `src/App/Api.php`, `src/Model/UserTask.php`, `src/App/Router.php`
**Action** :
- `isset($arr[$key])` → `array_key_exists($key, $arr)`
- `isset($var)` → `$var !== null`
**Statut** : ✅ Partiellement fait

### 1.3 `empty()` → comparaison explicite
**Fichiers** : `src/App/Api.php`, `src/App/Router.php`, `src/Controller/Task.php`
**Action** :
- `empty($arr)` → `$arr === []`
- `empty($str)` → `$str === ''`
- `empty($var)` → `$var === null || $var === '' || $var === []`
**Statut** : ✅ Partiellement fait

### 1.4 `__wakeup()` visibility
**Fichiers** : `src/App/SingletonAbstract.php`, `src/App/MultitonAbstract.php`
**Action** : `private function __wakeup()` → `public function __wakeup()`
**Statut** : ✅ Fait

### 1.5 `getenv()` return type
**Fichier** : `src/Config/DB.php`
**Action** : `getenv('X') ?: 'default'` → `\is_string(getenv('X')) ? getenv('X') : 'default'`
**Statut** : ✅ Fait

### 1.6 Shorthand ternary `?:` → `??`
**Fichier** : `src/Config/DB.php`
**Action** : `getenv('X') ?: 'default'` → `getenv('X') !== false ? getenv('X') : 'default'`
**Statut** : ✅ Fait

### 1.7 Closures → arrow functions
**Fichier** : `src/Config/Route.php`
**Action** : `function (...) use (...) { return ...; }` → `fn(...) => [...]`
**Statut** : ✅ Fait

### 1.8 `call_user_func_array` → spread operator
**Fichier** : `src/App/Router.php`
**Action** : `call_user_func_array($fn, $params)` → `$fn(...$params)`
**Statut** : ✅ Fait

### 1.9 Redundant `use` statements
**Fichier** : `src/Model/Task.php`
**Action** : Supprimer `use G4\Api\Model\TaskStatus;` (meme namespace)
**Statut** : ✅ Fait

## Phase 2 : Corrections de types (medium effort)

### 2.1 `mixed` → types explicites dans les controleurs
**Fichier** : `src/Controller/Task.php`
**Action** :
```php
// Avant
->setTitle($this->getParam('title'))
->setDescription($this->getParam('description', ''))

// Apres
$title = $this->getParam('title');
\assert(\is_string($title));
->setTitle($title)
```
**Statut** : ⚠️ Partiellement fait

### 2.2 String concatenation avec `mixed`
**Fichier** : `src/Controller/Task.php`
**Action** :
```php
// Avant
return 'User [' . $this->getParam('userId') . '] not exists';

// Apres
$userId = (int) $this->getParam('userId');
return 'User [' . $userId . '] not exists';
```
**Statut** : ⚠️ Partiellement fait

### 2.3 `$this->db` nullable
**Fichier** : `src/Model/UserTask.php`
**Action** : Ajouter `\assert($this->db !== null);` au debut de chaque methode utilisant `$this->db`
**Statut** : ⚠️ Partiellement fait

## Phase 3 : Refactoring structurel (high effort)

### 3.1 `Api.php` - Complexite cyclomatique (45 > 15)
**Fichier** : `src/App/Api.php`
**Probleme** : 11 methodes, complexite 45, Halstead volume 1122
**Action** :
- Extraire `processRequest()` en sous-methodes :
  - `detectHttpMethod()`
  - `resolveRoute()`
  - `instantiateController()`
  - `executeAction()`
- Extraire `cleanRequest()` dans une classe `RequestParser`
**Effort** : ~4h

### 3.2 `Task.php` (Controller) - Complexite (20 > 15)
**Fichier** : `src/Controller/Task.php`
**Probleme** : 7 methodes publiques dans un seul controleur
**Action** :
- Separer en `TaskController` (CRUD) et `UserTaskController` (associations)
- Ou extraire un `TaskService` pour la logique metier
**Effort** : ~2h

### 3.3 `UserTask.php` - Trop de methodes (15 > 10)
**Fichier** : `src/Model/UserTask.php`
**Probleme** : Melange agregat + repository + DAO
**Action** :
- Separer `UserTask` (entite) de `UserTaskRepository` (acces DB)
- Deplacer `save()`, `deleteUserTask()` dans le repository
**Effort** : ~3h

## Phase 4 : Polish (low priority)

### 4.1 Literal named arguments
**Fichiers** : `tests/ApiTest.php`, `src/App/Api.php`
**Action** : `in_array($x, $y, true)` → `in_array(needle: $x, haystack: $y, strict: true)`
**Note** : Purement cosmetique, peut etre ignore via config Mago

### 4.2 Early continue pattern
**Fichier** : `src/App/Api.php` (loops)
**Action** : Inverser les conditions pour reduire le nesting
**Note** : Cosmetique

### 4.3 Tests PHPUnit → attributs PHP 8
**Fichier** : `tests/ApiTest.php`
**Action** : Annotations `@test` → attributs `#[Test]`
**Note** : Optionnel

## Resume d'avancement

| Phase | Statut | Lignes modifiees | Fichiers |
|-------|--------|-----------------|----------|
| 1.1   | ✅     | ~10             | 2        |
| 1.2   | ✅     | ~8              | 4        |
| 1.3   | ✅     | ~6              | 3        |
| 1.4   | ✅     | 2               | 2        |
| 1.5   | ✅     | 4               | 1        |
| 1.6   | ✅     | 3               | 1        |
| 1.7   | ✅     | 7               | 1        |
| 1.8   | ✅     | 3               | 1        |
| 1.9   | ✅     | 1               | 1        |
| 2.1   | ⚠️     | ~15             | 1        |
| 2.2   | ⚠️     | ~10             | 1        |
| 2.3   | ⚠️     | ~6              | 1        |
| 3.1   | ❌     | ~100            | 3        |
| 3.2   | ❌     | ~50             | 3        |
| 3.3   | ❌     | ~80             | 3        |
| 4.x   | ❌     | ~30             | 2        |

## Prochaines etapes recommandees

1. **Terminer la Phase 2** (~1h) - Corrections de types dans `Task.php` et `UserTask.php`
2. **Phase 3.2** (~2h) - Separer `TaskController` en deux controleurs
3. **Phase 3.3** (~3h) - Separer entite et repository pour `UserTask`
4. **Phase 3.1** (~4h) - Refactorer `Api.php` (le plus gros morceau)
