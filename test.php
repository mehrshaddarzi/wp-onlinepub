<?php
namespace App\Admin\Page;

use App\ACL\ACL;
use App\Admin\Ui;
use App\Config\Front;
use App\ORM\TicketORM;
use App\Project\Helper;

class Ticket {

	public $pagelink;
	protected static $instance = NULL;

	/** Class constructor */
	public function __construct() {

		$this->pagelink = admin_url('/admin.php?page=ticket');

	}


	/*
	 * get_instance for Get Varible
	 */
	public static function get_instance()
	{
		if ( NULL === self::$instance )
			self::$instance = new self;
		return self::$instance;
	}


	public function PageWrap() {
		echo '
          <div class="wrap">
            <h1><i class="fa fa-connectdevelop wrap_h1_icon"></i> '.get_admin_page_title();

		if(!isset($_GET['chat_id'])) {
			echo '<a href="'.add_query_arg(['sendpm' => 'true'],Ticket::get_instance()->pagelink).'" class="add-new-h2">ارسال پیام</a>';

		}

		echo '
            </h1>';
		self::index();
		echo '</div>';
	}


	public function index() {

		//Alert system
		Ticket::get_instance()->alert();

		if(isset($_GET['sendpm'])) {
			Ticket::get_instance()->sendpm();
		} elseif(isset($_GET['chat_id'])) {
			Ticket::get_instance()->chat_show();
		} else {
			Ticket::get_instance()->grid();
		}

	}


	public function chat_show()
	{

		$chat_id = $_GET['chat_id'];

		if(isset($_POST['ticket_title'])) {
			if(Ticket::get_instance()->send_to_db_ticket() ===true){
				echo Ui::get()->Notice("پیام با موفقیت ارسال گردید","success");
			}
		}

		echo '<div style="padding:30px; padding-top:8px;">
            <style>
            .log span { color:#d72626 !important; display:inline !important; }
            label { width: 100px;}
            .buttonText { font-size:11px; }
            label[for=ticket_attachment] { width:130px; }
            .media-body p { padding:0px; }
            </style>
            ';

		//List chat
		$query = TicketORM::orderBy('id', 'asc')->where('chat_id', '=', $chat_id)->get();
		if($query->count() >0) {

			echo '
	<div style="margin-top:20px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
	<i class="fa fa-inbox"></i> تاریخچه گفتگو &nbsp;<span class="text-muted">(عنوان : '.\App\Ticket\Ticket::get_instance()->GetTitleticker($chat_id).')</span>
	</div>';

			foreach($query as $item) {

				//Type
				$attachment = '';
				$class = 'left';
				$icon = "user";
				if($item['sender'] =="admin") {
					$icon = "admin";
					$class= 'right';
				}

				$thumbnil = '
                    <div class="media-'.$class.'" style="padding:0px;">
                   <img class="media-object" style="margin-'.($item['sender'] =="user" ? "right" : "left").': 10px;" src="'.Front::get_instance()->path.'img/chat/'.$icon.'_chat.png" alt="#">
                  </div>
                    ';

				//attachment
				if($item['file'] !=="") {
					$attachment = '<p class="text-left font-11"><a href="'.wp_get_attachment_url( $item['file'] ).'" target="_blank" '.($item['sender'] =="user" ? 'class="text-primary"' : 'style="color:#fff"').'><i class="fa fa-download"></i> '. basename ( get_attached_file( $item['file'] ) ).'</a></p>';
				}

				//Readded All commet for User
				TicketORM::where('id', $item['id'])->update(['read_admin' => 1]);

				echo '
                    <div class="media" style="width:85%; float:'.($item['sender'] =="user" ? "left" : "right").';">
                  '.($item['sender'] =="admin" ? $thumbnil : "").'
                  <div class="media-body" style="'.($item['sender'] =="admin" ? 'width: 95%;' : "").'padding-right: 15px;background: '.($item['sender'] =="user" ? '#fff' : "#25ae88; color:#fff;").';padding: 10px;border-radius: 5px;padding-left: 15px;">
                    <p class="rtl text-right font-11 '.($item['sender'] =="user" ? 'text-danger' : "").'">ارسال شده در تاریخ '.parsidate('Y/m/d ساعت H:i:s',$item['create_date'],$lang='per').($item['sender'] =="user" ? ' توسط '.ACL::get_instance()->FullName($item['user_id']) : '').'</p>
                    <p class="rtl text-right font-11">'.$item['comment'].'</p>
                    '.$attachment.'
                  </div>
                  '.($item['sender'] =="user" ? $thumbnil : "").'
                </div>
                <div class="clearfix"></div>
                    ';


				$user_id = $item['user_id'];
			}

			echo '<div style="height:15px;"></div>';
		}

		echo '
	<div style="margin-top:20px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
	<i class="fa fa-list-alt"></i> ارسال پاسخ
	</div>';

		//check ticket is close
		if(\App\Ticket\Ticket::get_instance()->is_close_ticket($chat_id) ===false) {



			echo '
<form method="post" action="" enctype="multipart/form-data" id="send_ticket">
<table class="form-table">
	<tbody>
	
	<input type="hidden" name="chat_id" value="'.$chat_id.'">
	<input type="hidden" name="user_id" value="'.$user_id.'">
	<input type="hidden" name="ticket_title" value="">
	
	<tr class="form-field">
		<th scope="row"><label for="user_login"> متن پیام <span class="text-danger">*</span></label></th>
		<td>';

			$content = '';
			$editor_id = 'ticket_comment';
			$settings = array( 'media_buttons' => false , 'textarea_rows' => 8 );
			wp_editor( $content, $editor_id, $settings );

			// <textarea style="font-size: 12px; min-height:150px; width: 300px;" name="ticket_comment" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required"></textarea>
			echo '
        </td>
	</tr>
	
	
	<tr class="form-field">
		<th scope="row"><label for="user_login"> فایل ضمیمه <span class="text-danger">*</span></label></th>
		<td>
        <input type="file" id="ticket_attachment" name="ticket_attachment" class="form-control ltr input-group filestyle" data-file="input-file">
        <span style="display: block;font-size: 11px;margin-top: 6px;color: #828282;display:block;">'.per_number('حداکثر حجم فایل : 5 مگابایت , پسوند های قابل قبول شامل Zip,jpg,pdf').'</span>
        </td>
	</tr>
	
	
	<tr class="form-field">
		<td>
		<input class="btn btn-default" id="send-user-ticket" value="ارسال پاسخ" type="submit" style="font-size:11px;">

        </td>
	</tr>
	

	</tbody>
</table>
</form>';

		} else {
			echo "<div style='margin-top:25px;'>این گفتگو بسته شده است</div>";
		}


		echo '</div>';
	}


	function send_to_db_ticket(){

		//File
		$attachment = "";
		if ($_FILES['ticket_attachment']['name'] !== '') {
			$attachment = Helper::get_instance()->Uploadfile('ticket_attachment');
		}

		//chat Id
		if ($_POST['chat_id'] == "0") {
			$chatid = time();
		} else {
			$chatid = $_POST['chat_id'];
		}

		//No error Sentto Db
		$ticket = TicketORM::create([
			'user_id' => $_POST['user_id'],
			'title' => trim($_POST['ticket_title']),
			'create_date' => current_time('mysql'),
			'comment' => stripslashes($_POST['ticket_comment']),
			'sender' => 'admin',
			'read_admin' => 1,
			'read_user' => 0,
			'file' => $attachment,
			'chat_id' => $chatid,
		]);
		$ticket->save();
		if ($ticket->exists) {
			return true;
		}
	}


	public function sendpm()
	{

		if(isset($_POST['ticket_title'])) {
			if(Ticket::get_instance()->send_to_db_ticket() ===true){
				Ui::get()->jqueryRedirect(add_query_arg(['send_from_admin' => 'yes'],Ticket::get_instance()->pagelink), $echo = true);
				exit;
			}
		}

		echo '
<form method="post" action="" enctype="multipart/form-data" id="send_ticket">
<table class="form-table">
	<tbody>
	
	<input type="hidden" name="chat_id" value="0">
	<tr class="form-field">
		<th scope="row"><label for="user_login"> ارسال به کاربر <span class="text-danger">*</span></label></th>
		<td>
		<select type="text" name="user_id" style="width: 300px;" class="form-control rtl input-group">
          '.ACL::get_instance()->GetUserList(['tamirkar', 'kharidar']).'
        </select>
        </td>
	</tr>
	
	
	<tr class="form-field">
		<th scope="row"><label for="user_login"> عنوان پیام <span class="text-danger">*</span></label></th>
		<td>
		<input type="text" name="ticket_title" style="width: 300px;" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required">
        <span style="display: block;font-size: 11px;margin-top: 6px;color: #828282;display:block;">لطفا به فارسی تایپ کنید</span>
        </td>
	</tr>
	
	
	<tr class="form-field">
		<th scope="row"><label for="user_login"> متن پیام <span class="text-danger">*</span></label></th>
		<td>';

		$content = '';
		$editor_id = 'ticket_comment';
		$settings = array( 'media_buttons' => false , 'textarea_rows' => 8 );
		wp_editor( $content, $editor_id, $settings );

		// <textarea style="font-size: 12px; min-height:150px; width: 300px;" name="ticket_comment" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required"></textarea>
		echo '
        </td>
	</tr>
	
	
	<tr class="form-field">
		<th scope="row"><label for="user_login"> فایل ضمیمه <span class="text-danger">*</span></label></th>
		<td>
        <input type="file" id="ticket_attachment" name="ticket_attachment" class="form-control ltr input-group filestyle" data-file="input-file">
        <span style="display: block;font-size: 11px;margin-top: 6px;color: #828282;display:block;">'.per_number('حداکثر حجم فایل : 5 مگابایت , پسوند های قابل قبول شامل Zip,jpg,pdf').'</span>
        </td>
	</tr>
	
	
	<tr class="form-field">
		<td>
		<input class="btn btn-default" id="send-user-ticket" value="ارسال تیکت" type="submit" style="font-size:11px;">

        </td>
	</tr>
	

	</tbody>
</table>
</form>';


	}


	public function alert()
	{
		if(isset($_GET['send_from_admin'])) {
			echo Ui::get()->Notice("پیام با موفقیت ارسال گردید","success");
		}
	}



	public function grid()
	{
		global $wpdb;

//Page url
		$page_url = Ticket::get_instance()->pagelink;
		$table = $wpdb->prefix.'ticket';
		$pagination_item_number = 15;

		$page_for_click = '';
		if (isset($_GET['paging'])) { $page_for_click = '&amp;paging='.$_GET['paging']; }


		/*
		 * Jquery and Front
		 */
		echo '
<script type="text/javascript">
    jQuery(document).ready(function($){
        
    });
</script>
<style>
    tr[class=list] { transition:all 1s; }
    tr[class=list]:hover { background-color:rgba(204,204,204,0.3); }
    .active-tr { background-color:rgba(106,143,226,0.2); }
</style>';



//include pagination
		include( str_replace("\\","/", get_template_directory() . '/includes/pagination.class.php') );


//**********************************Search Site
		$search_field = [
			'title' => ['name' => 'عنوان', 'compare' => 'like'],
			'comment' => ['name' => 'متن پیام', 'compare' => 'like'],
			'user_id' => ['name' => 'نام و نام خانوادگی', 'compare' => 'like'],
		];

		if (isset($_POST['s']) || isset($_GET['s'])) {

			if (isset($_GET['s'])) { // POST OR GET for Search
				$search = trim($_GET['s']);
				$name_search = trim($_GET['s']);
				$field = trim($_GET['field']);
			} else {
				$search = trim($_POST['s']);
				$name_search = trim($_POST['s']);
				$field = trim($_POST['field']);
			}

//Search Engine
			$sql_search ="SELECT * FROM `".$table."` WHERE ";

//user_id
			if($field =="user_id") {
				$sql_search .="(";
				$list_user = ACL::get_instance()->SearchByNiceName($search);
				$count_list = count($list_user);
				if($count_list >0) {
					$u = 1;
					foreach ($list_user as $l) {
						$sql_search .=' user_id = '.$l;
						if($count_list !=$u) { $sql_search .=' OR '; }
						$u++;
					}
				} else {
					$sql_search .=' user_id = 0';
				}
				$sql_search .=")";
			}

			if($field =="title" || $field =="comment") {
				$sql_search .=" ".$field." LIKE  '%$search%'";
			}
			$sql_search .=" GROUP by `chat_id` ORDER BY `id` DESC";

			$search_count = $wpdb->get_results($sql_search);
			$items = count($search_count);
		} else {

			$sql = "SELECT * FROM `".$table."` GROUP by `chat_id` ORDER BY `id` DESC";
			$Query = $wpdb->get_results($sql, ARRAY_A);
			$rowcount = count($Query);
			//$items = $wpdb->get_var("SELECT COUNT(*) FROM `".$table."` GROUP by `chat_id` ORDER BY `id` DESC");
			$items = $rowcount;

		}


//Show List
		if($items > 0) {
			$p = new \wppagination;
			$p->items($items);
			$p->limit($pagination_item_number);
			$p->target($page_url);
			if (isset($_POST['s']) || isset($_GET['s'])) { $p->target($page_url."&s=".$name_search.'&field='.$field);} else { $p->target($page_url); }
			$p->currentPage($_GET[$p->paging]);
			$p->calculate();
			$p->parameterName('paging');
			$p->adjacents(1);
			$p->nextLabel('صفحه بعدی');
			$p->prevLabel('صفحه قبلی');
			if(!isset($_GET['paging'])) { $p->page = 1;} else { $p->page = $_GET['paging'];}
			$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		}

		?>


		<div style="margin-bottom:35px;"></div>

		<form action="<?php echo $page_url; ?>" method="post" class="search-form" autocomplete="off">
			<p class="search-box" style="padding-bottom:5px; ">
				<input type="text" name="s" value="" class="search-input" id="s" placeholder="عبارت را وارد کنید ..." style="height: 29px;width: 250px;" />

				<select name="field">
					<?php
					foreach($search_field as $se => $se_v) {
						echo '<option value="'.$se.'">'.$se_v['name'].'</option>';
					}
					?>
				</select>
				<input type="submit" value="جست‌وجو" class="button" style="height: 28px;" />
			</p>
		</form>

		<?php
		if (isset($_POST['s']) || isset($_GET['s'])) {
			if ($items > 0) {
				echo '<div dir="rtl">موارد یافت شده : ' . per_number($items) . '</div>';
			}
		}
		?>

		<div style="clear:both;"></div>

		<table class="widefat">
			<thead>
			<tr>
				<th width="50"></th>
				<th width="150">نام و نام خانوادگی</th>
				<th>عنوان</th>
				<th>متن</th>
				<th width="140">آخرین بروز رسانی</th>
				<th width="90">وضعیت</th>
				<th width="40"></th>
				<th width="40"></th>
			</tr>
			</thead>
			<tbody>

			<?php
			if (isset($_POST['s']) || isset($_GET['s'])) {
				$sql = $sql_search." ".$limit;
			} else {

				$sql = "SELECT * FROM `".$table."` GROUP by `chat_id` ORDER BY `id` DESC ".$limit; }
			$Query = $wpdb->get_results($sql, ARRAY_A);
			$rowcount = count($Query);
			if ($rowcount >0) {
				$radif = 0;
				foreach ( $Query as $row ) {
					$radif = $radif + 1;
					?>
					<?php
					echo '<tr class="list">';
					?>
					<td><?php echo per_number($radif); ?></td>
					<td><?php echo ACL::get_instance()->FullName($row['user_id']); ?></td>
					<td><?php echo per_number(\App\Ticket\Ticket::get_instance()->GetTitleticker($row['chat_id'])); ?></td>
					<td><?php echo per_number(Helper::get_instance()->wp_strip_text(Helper::get_instance()->mb_substr($row['comment'], 70))); ?> ..</td>
					<td><?php echo parsidate('l j F Y', $row['create_date'], 'per'); ?></td>
					<td class="text-danger">
						<?php
						echo \App\Ticket\Ticket::get_instance()->vaziat_ticket($row['chat_id'], 'admin');
						echo '<br>';
						if(\App\Ticket\Ticket::get_instance()->is_close_ticket($row['chat_id']) ===false) {
							echo '<a href="'.$page_url.'&close_ticket='.$row['chat_id'].'">بستن تیکت</a>';
						} else {
							echo '<a href="'.$page_url.'&open_ticket='.$row['chat_id'].'">باز کردن تیکت</a>';
						}
						?>
					</td>

					</tr>
				<?php }
			} else { ?>
			<tr>
				<th colspan="8">موردی یافت نشد !</th>
			<tr>
				<?php } ?>
			</tbody>
		</table>

		<?php
//Pagination
		if($items >$pagination_item_number) { ?>

			<div class="tablenav" style="margin-top:15px;"><div class='tablenav-pages'><?php echo $p->show();?></div></div>
			<div style="clear:both; margin-bottom:5px;"></div>

		<?php } ?>

		<div class="go-to-page" style="float:left; text-align:left; margin-bottom:8px; margin-top:<?php if($items >$pagination_item_number) { echo '-8'; } else { echo '10'; } ?>px;">
			برو به صفحه :
			<select onchange="if (this.value) window.location.href=this.value" style="paddign:0px; line-height:0px; height:29px;">
				<option value="">انتخاب کنید ...</option>
				<?php
				//show go to page
				for ($i=1; $i<=$p->calculate($show = 'number_all'); $i++) {
					$selected = '';
					if (isset($_GET['paging']) and $i==$_GET['paging']) { $selected= 'selected'; }

					$search = '';
					if (isset($_POST['s']) || isset($_GET['s'])) { $search= "&s=".$name_search."&field=".$field; }

					echo '<option value="'.$page_url.$search.'&paging='.$i.'" '.$selected.'>'.per_number($i).'</option>';
				}
				?>
			</select>
		</div>
		<div style="clear:both; margin-bottom:5px;"></div>

		<?php

	}

}