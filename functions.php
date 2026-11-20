<?php
require_once __DIR__ . '/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function h($text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function query(string $sql, array $params = []): PDOStatement { $s = database()->prepare($sql); $s->execute($params); return $s; }
function post(string $key): string { return trim((string)($_POST[$key] ?? '')); }
function go(string $page): void { header('Location: index.php?page=' . rawurlencode($page)); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals(csrf(), post('csrf'))) throw new RuntimeException('This form expired. Please refresh and try again.'); }
function current_rep(): int { return (int)($_SESSION['rep_id'] ?? 0); }
function require_login(): void { if (!current_rep()) go('login'); }
function rep_by_id(int $rep): ?array { $row=query('SELECT * FROM representatives WHERE rep_id=?', [$rep])->fetch(); return $row ?: null; }
function event_for_rep(int $id): ?array { $row=query('SELECT * FROM moving_events WHERE moving_id=? AND rep_id=?',[$id,current_rep()])->fetch();return $row ?: null; }
function valid_name(string $v): bool { return (bool)preg_match("/^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/", $v); }
function valid_phone(string $v): bool { return (bool)preg_match('/^\d{10}$/', preg_replace('/[\s()\-]/','',$v)); }
function valid_address(): bool { return (bool)preg_match('/^\d+[A-Za-z]?$/',post('street_number')) && post('street_name')!=='' && valid_name(post('city')) && (bool)preg_match('/^[A-Za-z]{2}$/',post('state')) && (bool)preg_match('/^\d{5}$/',post('zip')) && valid_phone(post('client_phone')); }
function client_lookup(): ?array { return query('SELECT * FROM clients WHERE client_id=? AND first_name=? AND last_name=?',[post('client_id'),post('first_name'),post('last_name')])->fetch() ?: null; }
function issue_moving_id(): int { for($i=0;$i<30;$i++){ $id=random_int(100000,999999);if(!(int)query('SELECT COUNT(*) FROM moving_events WHERE moving_id=?',[$id])->fetchColumn()) return $id; } throw new RuntimeException('Unable to generate a unique Moving ID. Please retry.'); }
function input(string $label,string $name,string $rule='',string $type='text',string $value='',bool $required=true): void { echo '<label for="'.h($name).'">'.h($label).'</label><input id="'.h($name).'" name="'.h($name).'" type="'.h($type).'" value="'.h($value).'" '.($required?'required ':'').'data-rule="'.h($rule).'" data-label="'.h($label).'">'; }
function start_form(string $operation,string $confirmation=''): void {echo '<form method="post" '.($confirmation?'data-confirm="'.h($confirmation).'"':'').'><input type="hidden" name="operation" value="'.h($operation).'"><input type="hidden" name="csrf" value="'.h(csrf()).'">';}
function finish_form(string $button): void { echo '<div class="actions"><button type="submit">'.h($button).'</button><button type="reset" class="secondary">Reset</button></div></form>'; }
function address_fields(bool $personal=false): void {echo '<div class="grid">';input($personal?'Street Number':'Delivery Street Number','street_number');input($personal?'Street Name':'Delivery Street Name','street_name');input($personal?'City':'Delivery City','city','name');input($personal?'State':'Delivery State','state','state');input($personal?'ZIP Code':'Delivery ZIP Code','zip','zip');if($personal)input('Client Phone Number','client_phone','phone');echo '</div>';}
function header_page(string $title, bool $menu=true): void { echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' | Moving On Up</title><link rel="stylesheet" href="assets/style.css"><script defer src="assets/script.js"></script></head><body><main class="card '.(!$menu?'login':'').'"><h1>Moving On Up</h1><p class="subtitle">'.h($title).'</p>';if($menu){echo '<nav>';foreach(['search'=>'Search Representative Account','verify'=>'Book Moving Event','cancel'=>'Cancel Moving Event','instructions'=>'Special Instructions','update'=>'Update Instructions','create'=>'Create Client Account','logout'=>'Log Out'] as $p=>$label)echo '<a href="index.php?page='.h($p).'">'.h($label).'</a>';echo '</nav>';}if(isset($_SESSION['flash'])) {echo '<p class="message success">'.h($_SESSION['flash']).'</p>';unset($_SESSION['flash']);}}
function footer_page(): void { echo '<p class="footer">Moving On Up &copy; 2026 · IT202 Project 2</p></main></body></html>'; }
function client_fields(): void { echo '<div class="grid">';input('Client First Name','first_name','name');input('Client Last Name','last_name','name');input('Client ID (4 digits)','client_id','id');echo '</div>'; }
function moving_id_field(): void { input('Moving ID (6 digits)','moving_id'); }
function instructions_field(): void { echo '<label for="instructions">Packing specifications and special requests</label><textarea id="instructions" name="instructions" required maxlength="2000" placeholder="Packing materials, fragile items, placement in new home, etc."></textarea>'; }
