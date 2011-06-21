<?php
class ViewHelper {

	static public function getMessageHtml($messages = null, $map = null) {
		
		if($messages == null) {
			return  "
						<div class=\"message\" style=\"padding:2px\">
							You need to login to see the discussion.
						</div>
					";
		
		}
				
		foreach($messages as $msg) { 
			
			$total = isset($map[$msg['id']]) ? '('.$map[$msg['id']].')</a> <a href="#" id="m_'.$msg['id'].'"><img src="template/rebel/img/detail.jpg" />' : '';
			
			if($total != '') {
				$comments = MessageDAO::run(MessageDAO::$getCommentByMessageId, $msg['id']);	
			}
			
			$result .= "
			<div class=\"text\">
				<a href=\"/message/show/{$msg['id']}\">".stripslashes($msg['title'])." {$total} </a>
					<strong>-</strong> {$msg['name']}  
				
			</div>
			";	
			
			if($total != '') {
				foreach($comments as $c) {
					$result .= "
						<div class=\"hk cm_{$msg['id']}\">
							<div class=\"title\">{$c['name']} said: </div>
							<div class=\"comment\">".stripslashes($c['comment'])."</div>
						</div>	
					";		
				}	
			}	
		}
		
		return $result;
	}
	
	
	static public function getMessageDetailHtml($messages = null, $comments = null) {
		
		$result .= "<div id=\"content_detail\" class=\"text\">".stripslashes(nl2br($messages[0]['content']))."<br /><hr /><br /></div>";			
		
		if($comments != null) {
						
			
			foreach($comments as $c) { 
				$result .= "
					<div class=\"text\">
						<strong>{$c['name']} said:</strong> <br />".stripslashes($c['comment'])."
					</div>
					";	
			}		
		} else {
			$result .= "
				<div class=\"text\">
					<h3>Be the first one to leave comment ~</h3>
				</div>				
			";				
		}
		
		return $result;
	}	
	
	static public function getTutorialHtml($user = null) {
		$return .= '
			<div class="title">
				<a id="sjcac" href="#">Tutorial
			';	

		if($user != null) { 
			$return .= " for {$user['name']} "; 
		 } 

		$return .= '
				</a>
			</div>
			<div id="detail" class="english sjcac">
			   <div id="function">
				   <div class="map">
			';	   
		
		$return .= "  Hi {$user['name']}, the concept is pretty easy ~ In order to add discussions or comments, you need to register an account in <a href=\"http://forum.iroy2000.blogdns.com\">forum</a>. <br /><br />
						Then you can request an <a href=\"/mail/auth\">access link</a> send to your email. All you need to do is clicking the link in your email, and you will be automatically login. You just need that same link to login everytime.<br /><br /> 
						~ Never need a login and password again ~ 
				   </div>
			   </div>
			</div>		
		";	
		
		return $return;
	}

}
?>