<?php
require_once __DIR__ . '/functions.php';
$page = (string)($_GET['page'] ?? (current_rep() ? 'search' : 'login'));
$pages = ['login','search','verify','book','cancel','instructions','update','create','personal','logout'];
if (!in_array($page,$pages,true)) $page='search';
if ($page==='logout') {session_unset(); session_regenerate_id(true);go('login');}
if ($page!=='login') require_login();
if ($page==='login' && current_rep()) go('search');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        check_csrf(); $op=post('operation');
        if ($op==='login') {
            if (!valid_name(post('first_name'))||!valid_name(post('last_name'))||!preg_match('/^\d{4}$/',post('rep_id'))||!valid_phone(post('phone'))||post('password')==='') throw new RuntimeException('Please enter valid login information.');
            if (!preg_match('/^\d{1,5}$/',post('extension'))) throw new RuntimeException('Please enter a numeric phone extension.');
            if (isset($_POST['email_confirmation']) && !filter_var(post('email'),FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address if email confirmation is checked.');
            $rep=query('SELECT * FROM representatives WHERE rep_id=? AND first_name=? AND last_name=? AND phone=? AND extension=?',[post('rep_id'),post('first_name'),post('last_name'),preg_replace('/\D/','',post('phone')),post('extension')])->fetch();
            if (!$rep || !password_verify(post('password'),$rep['password_hash']) || (isset($_POST['email_confirmation']) && strcasecmp(post('email'),$rep['email'])!==0)) throw new RuntimeException('Representative account not found. Please check your information.');
            session_regenerate_id(true); $_SESSION['rep_id']=(int)$rep['rep_id'];
            $next=post('transaction'); if(!in_array($next,['search','verify','cancel','instructions','update','create'],true))$next='search';go($next);
        }
        require_login();
        if ($op==='verify') {
            if (!preg_match('/^\d{4}$/',post('client_id'))||!valid_name(post('first_name'))||!valid_name(post('last_name'))) throw new RuntimeException('Please enter the client name and four-digit ID correctly.');
            $client=client_lookup();
            if (!$client) { $_SESSION['missing_client']=true; throw new RuntimeException('CLIENT CANNOT BE FOUND. You can re-enter the data or create a new client account.'); }
            $_SESSION['booking_client']=(int)$client['client_id'];flash('Client verified: '.$client['first_name'].' '.$client['last_name'].'. You may book a moving event.');go('book');
        }
        if ($op==='book') {
            $client=(int)($_SESSION['booking_client']??0);
            if (!$client || !(int)query('SELECT COUNT(*) FROM clients WHERE client_id=?',[$client])->fetchColumn()) throw new RuntimeException('Please verify the client before booking.');
            if(!preg_match('/^\d+[A-Za-z]?$/',post('street_number'))||post('street_name')===''||!valid_name(post('city'))||!preg_match('/^[A-Za-z]{2}$/',post('state'))||!preg_match('/^\d{5}$/',post('zip'))) throw new RuntimeException('Please correct the delivery address.');
            $date=DateTimeImmutable::createFromFormat('!Y-m-d',post('moving_date')); if(!$date||$date->format('Y-m-d')!==post('moving_date'))throw new RuntimeException('Enter a valid moving date.');
            foreach(['weight','miles','total_cost'] as $n)if(!is_numeric(post($n))||(float)post($n)<=0)throw new RuntimeException('Weight, miles, and cost must be positive numbers.');
            $id=issue_moving_id();
            query('INSERT INTO moving_events(moving_id,rep_id,client_id,to_street_number,to_street_name,to_city,to_state,to_zip,moving_date,total_weight,miles,total_cost) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',[$id,current_rep(),$client,post('street_number'),post('street_name'),post('city'),strtoupper(post('state')),post('zip'),post('moving_date'),post('weight'),post('miles'),post('total_cost')]);
            unset($_SESSION['booking_client']);$_SESSION['last_moving_id']=$id;flash('Moving Event Booked! Moving ID: '.$id.'. You may now add special instructions.');go('instructions');
        }
        if ($op==='cancel') {
            if(!preg_match('/^\d{6}$/',post('moving_id')))throw new RuntimeException('Enter a six-digit Moving ID.');
            $event=event_for_rep((int)post('moving_id'));if(!$event)throw new RuntimeException('Moving ID does not exist for this representative. Please re-enter it.');
            query('DELETE FROM moving_events WHERE moving_id=? AND rep_id=?',[(int)post('moving_id'),current_rep()]);
            flash('Moving Event and its Special Instructions cancelled. Representative ID: '.current_rep().', Client ID: '.$event['client_id'].', Moving ID: '.$event['moving_id']);go('cancel');
        }
        if ($op==='instructions') {
            if(!preg_match('/^\d{6}$/',post('moving_id')))throw new RuntimeException('Enter a valid Moving ID.');
            $event=event_for_rep((int)post('moving_id'));if(!$event)throw new RuntimeException('MOVING INFORMATION CANNOT BE FOUND. Check the Moving ID and confirm the client booked an event.');
            if(post('instructions')==='') throw new RuntimeException('Please enter special instructions.');
            if(query('SELECT 1 FROM special_instructions WHERE moving_id=?',[$event['moving_id']])->fetch()) {flash('Special instructions already exist; please use Update Special Instructions.');go('update');}
            query('INSERT INTO special_instructions(moving_id,instructions) VALUES(?,?)',[$event['moving_id'],post('instructions')]);
            flash('Special instructions were added to your Moving Event.');go('instructions');
        }
        if ($op==='update') {
            if(!preg_match('/^\d{6}$/',post('moving_id')))throw new RuntimeException('Enter a valid Moving ID.');
            $event=event_for_rep((int)post('moving_id'));if(!$event)throw new RuntimeException('No moving event found for this representative and Moving ID.');
            if(post('instructions')==='')throw new RuntimeException('Enter the additional instructions.');
            $old=query('SELECT instructions FROM special_instructions WHERE moving_id=?',[$event['moving_id']])->fetchColumn();
            if($old===false) query('INSERT INTO special_instructions(moving_id,instructions) VALUES(?,?)',[$event['moving_id'],post('instructions')]);
            else query('UPDATE special_instructions SET instructions=? WHERE moving_id=?',[trim($old."\n".post('instructions')),$event['moving_id']]);
            flash('Client additional special instructions updated.');go('update');
        }
        if ($op==='create') {
            if(!valid_name(post('first_name'))||!valid_name(post('last_name'))||!preg_match('/^\d{4}$/',post('client_id')))throw new RuntimeException('Enter a valid client first name, last name, and four-digit ID.');
            if(query('SELECT 1 FROM clients WHERE client_id=?',[post('client_id')])->fetch())throw new RuntimeException('Client already has an account.');
            query('INSERT INTO clients(client_id,first_name,last_name) VALUES(?,?,?)',[post('client_id'),post('first_name'),post('last_name')]);
            $_SESSION['new_client']=(int)post('client_id');flash('Client Account Created. Please enter the client personal information.');go('personal');
        }
        if ($op==='personal') {
            $client=(int)($_SESSION['new_client']??0);
            if(!$client)throw new RuntimeException('First create a client account.');
            if(!valid_address())throw new RuntimeException('Please enter a valid street address, city, two-letter state, ZIP, and 10-digit phone.');
            query('INSERT INTO client_personal_info(client_id,street_number,street_name,city,state,zip,phone) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE street_number=VALUES(street_number),street_name=VALUES(street_name),city=VALUES(city),state=VALUES(state),zip=VALUES(zip),phone=VALUES(phone)',[$client,post('street_number'),post('street_name'),post('city'),strtoupper(post('state')),post('zip'),preg_replace('/\D/','',post('client_phone'))]);
            unset($_SESSION['new_client']);flash('Client Information Record created. You can now book a moving event.');go('verify');
        }
        throw new RuntimeException('Invalid form request.');
    } catch(Throwable $e) { $error=$e instanceof PDOException ? 'Database error. Check your database setup, permissions, and imported tables.' : $e->getMessage(); }
}
$titles=['login'=>'Representative Login','search'=>'Search Representative Account','verify'=>'Verify Client for Booking','book'=>'Book a Client Moving Event','cancel'=>'Cancel a Moving Event','instructions'=>'Special Instructions for the Move','update'=>'Update Special Instructions','create'=>'Create New Client Account','personal'=>'Client Personal Information'];
header_page($titles[$page] ?? 'Moving On Up',$page!=='login');
if($error)echo '<p class="message error">'.h($error).'</p>';
switch($page) {
case 'login':
    echo '<p class="note">Use the existing Moving On Up representative account from Project 1. All values are checked against MySQL.</p>';start_form('login');echo '<div class="grid">';input('First Name','first_name','name');input('Last Name','last_name','name');echo '</div><label>Password</label><div class="password-box"><input id="password" name="password" type="password" required><button type="button" id="togglePassword" class="secondary">Show</button></div><div class="grid">';input('Representative ID','rep_id','id');input('Phone Number (10 digits)','phone','phone');input('Phone Extension','extension');input('Email Address','email','email','text','',false);echo '</div><div class="checkbox"><input type="checkbox" name="email_confirmation" id="email_confirmation"><label for="email_confirmation">Request email confirmation (verify email on account)</label></div><label for="transaction">Select Transaction</label><select name="transaction" id="transaction" required><option value="">Choose a transaction</option>';foreach(['search'=>'Search A Moving Company Representative Account','verify'=>'Book a Client Moving Event','cancel'=>'Cancel a Client Moving Event','instructions'=>'Special Instructions for the Move','update'=>'Update Special Instructions','create'=>'Create a New Client Account'] as $k=>$v)echo '<option value="'.h($k).'">'.h($v).'</option>';echo '</select>';finish_form('Log In');break;
case 'search':
    echo '<h2>Representative and Associated Client Records</h2><p class="mini">Only clients and moving events associated with your logged-in representative are shown. Results use SQL JOINs.</p>';
    try {$rows=query('SELECT r.first_name rep_first,r.last_name rep_last,r.rep_id,r.phone rep_phone,r.email rep_email,c.first_name client_first,c.last_name client_last,c.client_id,p.street_number,p.street_name,p.city,p.state,p.zip,p.phone client_phone,m.to_street_number,m.to_street_name,m.to_city,m.to_state,m.to_zip,m.moving_date,m.total_weight,m.miles,m.total_cost,m.moving_id,s.instructions FROM representatives r LEFT JOIN moving_events m ON m.rep_id=r.rep_id LEFT JOIN clients c ON c.client_id=m.client_id LEFT JOIN client_personal_info p ON p.client_id=c.client_id LEFT JOIN special_instructions s ON s.moving_id=m.moving_id WHERE r.rep_id=? ORDER BY m.moving_id',[current_rep()])->fetchAll();echo '<div class="table-wrap"><table><thead><tr>'; $head=['Rep Name','Rep ID','Rep Phone','Rep Email','Client Name','Client ID','Client Address','Client Phone','New Address','Move Date','Weight','Miles','Total Cost','Moving ID','Special Instructions'];foreach($head as $v)echo '<th>'.h($v).'</th>';echo '</tr></thead><tbody>';foreach($rows as $r){echo '<tr>'; $values=[$r['rep_first'].' '.$r['rep_last'],$r['rep_id'],$r['rep_phone'],$r['rep_email'],trim(($r['client_first']??'').' '.($r['client_last']??'')),$r['client_id'],trim(($r['street_number']??'').' '.($r['street_name']??'').', '.($r['city']??'').', '.($r['state']??'').' '.($r['zip']??'')),$r['client_phone'],trim(($r['to_street_number']??'').' '.($r['to_street_name']??'').', '.($r['to_city']??'').', '.($r['to_state']??'').' '.($r['to_zip']??'')),$r['moving_date'],$r['total_weight'],$r['miles'],$r['total_cost'],$r['moving_id'],$r['instructions']];foreach($values as $v)echo '<td>'.h($v??'').'</td>';echo '</tr>';}echo '</tbody></table></div>';}
    catch(Throwable $e){echo '<p class="error message">Cannot load the database table. Check config.php and import the SQL file.</p>';}break;
case 'verify':
    echo '<p class="note">Check whether the client has an account before booking.</p>';start_form('verify');client_fields();finish_form('Verify Client');echo '<p class="mini">Client missing? <a href="index.php?page=create">Create a New Client Account</a> or try again.</p>';break;
case 'book':
    if(!isset($_SESSION['booking_client'])){echo '<p class="error message">First verify your client.</p><a class="btn" href="index.php?page=verify">Verify a Client</a>';break;}
    echo '<p class="note">Verified Client ID: '.h($_SESSION['booking_client']).'. A random unique Moving ID will be issued after booking.</p>';start_form('book','You are about to REQUEST a Moving Event. Are you sure you want to do so?');address_fields();echo '<div class="grid">';input('Date of Moving Event','moving_date','date','date');input('Total Weight of Items to Move','weight','positive','number');input('Miles for Move','miles','positive','number');input('Total Cost ($)','total_cost','positive','number');echo '</div>';finish_form('Book Moving Event');break;
case 'cancel':
    echo '<p class="note">Cancelling a moving event also deletes its special instructions. Client accounts remain.</p>';start_form('cancel','You are about to CANCEL this moving event. Special instructions for this moving event will also be deleted. Continue?');moving_id_field();finish_form('Cancel Moving Event');break;
case 'instructions':
    if(isset($_SESSION['last_moving_id']))echo '<p class="note">Your latest booked Moving ID is <strong>'.h($_SESSION['last_moving_id']).'</strong>. Do you want to give moving instructions? Enter that ID below, or choose any valid existing Moving ID.</p>';
    start_form('instructions','You are about to REQUEST Special Instructions for your client. Continue?');moving_id_field();instructions_field();finish_form('Add Special Instructions');break;
case 'update':
    echo '<p class="note">New instructions are added to any existing instructions for the booking.</p>';start_form('update','You are about to UPDATE the Special Instructions of the client. Continue?');moving_id_field();instructions_field();finish_form('Update Special Instructions');break;
case 'create':
    start_form('create');client_fields();finish_form('Create Account');break;
case 'personal':
    if(!isset($_SESSION['new_client'])){echo '<p class="note">Create the client account first.</p><a class="btn" href="index.php?page=create">Create Client</a>';break;}
    echo '<p class="note">Enter personal information for new Client ID '.h($_SESSION['new_client']).'.</p>';start_form('personal');address_fields(true);finish_form('Save Client Information');break;
}
footer_page();
