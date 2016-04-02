<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>API Test</title>

	<style type="text/css">

	::selection{ background-color: #E13300; color: white; }
	::moz-selection{ background-color: #E13300; color: white; }
	::webkit-selection{ background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	
	#container, .container{
		margin: 10px;
		padding: 10px;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	.clear {clear: both;}
	.left {float: left;}
	.right {float: right;}
	</style>
	<script type="text/javascript" src="<?php echo base_url(); ?>/js/jquery-1.4.4.min.js"></script>
	<script>
	var ajax_actions_url = '<?php echo base_url();?>' + "api/test/test_api_call";
	var token = '<?php echo $token; ?>';
	
	function testApiCall(){
		$.ajax({
			type : 'POST',
			dataType : 'json',
			url : ajax_actions_url,
			data : {
				//'action' : 'test_api_call',
				'query_string' : $('#queryString').val(),
				'token' : token,
				'post_data' : $('#postData').val()
			},
			success : function(data) {
				console.log('success ', data);
				$('#testCallResult').html(data.output);
				token = data.token;
				$('#token').html(token);
			},
			error : function(data) {
				console.log('error ', data);
				if (typeof data == 'object' && data.token) {
					token = data.token;
					$('#token').html(token);
				}
			}
		});
	}
	
	$(document).ready(function(){	
		$('#token').html(token);	
		$('#submitBtn').click(function(){
			testApiCall();		
		});
	});
	</script>
</head>
<body>

<div id="container">
	<h1>API test</h1>

	<div id="body">
		<p>
			<?php if (is_array($data)) {
				foreach($data as $key => $value) {
					echo $key.":\t"; var_export($value);
					echo '<br/></br>';
				}
			}
			else var_export($data); ?>
		</p>
	</div>
	<?php if ( ! empty($showTestForm)) : ?>
	<div class="container">
		<div id="testForm" class="left">
			<div>Query String:</div>
			<div><input type="text" id="queryString" style="width:650px;"></div>
			<div>Post Data:</div>
			<div><textarea id="postData" rows="10" cols="70" style="width:650px;"></textarea></div>
			<div><input type="button" id="submitBtn" value="Send"></div>
		</div>
		<div class="left container" style="width:280px; margin:20px;">Token:&nbsp;<span id="token"></span></div>
		<div class="clear"></div>
		<div id="testCallResult" class="container"></div>
	</div>
	<?php endif; ?>
	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>