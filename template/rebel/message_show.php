<!--- Contents --->		
		
		<div id="content">
		  <!-- Title -->

			<div class="title-red">
				<a href="/message">Main</a> &nbsp; &gt; &nbsp; Comments(s)
		  </div>
		   <!-- Speaker Information -->
		    <div id="speaker">
				&nbsp;
			</div>
			
            <div class="bio">
				<?=ViewHelper::getMessageDetailHtml($view->messages, $view->comments)?>
			</div>
			
			<div class="footer">&nbsp;</div>

	     </div>
	  
	  
<!--- Right Slash --->

	  <!-- Events Date and Time -->
	  <div id="right_sash">
		<!-- <div class="showLogo"></div> -->
	  
	  <div class="title">
	  	<a id="svac" href="#">Re: <?=stripslashes($view->messages[0]['title'])?></a>
	  </div>
	  <div id="detail" class="english svac">
	  	 <div class="text showDetail">
		 	<?=stripslashes(nl2br($view->messages[0]['content']))?><br /><br />
			<? if($view->user != null) { ?>
				<hr />
			<? } ?>
		 </div>
		 
		 <? if ($view->error != null) { ?>
			<div id="messages" style="padding-top: 10px; margin-top:10px; color:#FF0000; font-weight:bold;">
				Error: <?=$view->error?>
			</div>
		<? } ?>	
		 
	  	<? if($view->user != null) { ?>
			<form action="<?php echo Util::getLink('/message/insert'); ?>" method="post">
			<br />
			<h3>Comment / Reply</h3>
			<textarea name="comment" cols="30" rows="5"></textarea>
			<br /><br />
			<input type="hidden" name="message_id" value="<?=$controller->getRouter()->getId()?>" />
			<input type="hidden" name="_submit" value="comment" />
			<input type="hidden" name="_form_secret" id="_form_secret" value="<?=$_SESSION['FORM_SECRET']?>" />
			<input type="submit" name="submit" value="Submit Comment" onclick="this.disabled=true,this.form.submit();" />
			</form>
		<? } else { ?>
			You need to login to submit comments ~
		<? } ?>
	   	   
	</div>
	<br />
	<?=ViewHelper::getTutorialHtml($view->user)?>
	</div>	  
	  
	  