<html lang="en">
	<head>
		<title>Auto publish</title>
		<link href="https://lipis.github.io/bootstrap-social/assets/css/bootstrap.css" rel="stylesheet">
		<link href="https://lipis.github.io/bootstrap-social/assets/css/font-awesome.css" rel="stylesheet">
		<link href="https://lipis.github.io/bootstrap-social/bootstrap-social.css" rel="stylesheet" >
		<style type="text/css">
			div.container {
				padding: 50px;
				width: 100%;
			}
			button.btn-social {
				width: 200px;
			}
		</style>
	</head>
	<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-2">
				<button data-link="/twitter/" class="btn btn-block btn-social btn-twitter"><span class="fa fa-twitter"></span> Connect Twitter</button>
			</div>
			<div class="col-sm-2">
				<button data-link="/facebook/" class="btn btn-block btn-social btn-facebook"><span class="fa fa-facebook"></span> Connect Facebook</button>
				<h5>Facebook Pages</h5>
				<select id="fbpages" class="form-control" multiple>
				</select>
			</div>
			<div class="col-sm-2">
				<button data-link="/linkedin/" class="btn btn-block btn-social btn-linkedin"><span class="fa fa-linkedin"></span> Connect LinkedIn</button>
			</div>
		</div>
		<br><br>
		<div class="row">
			<div class="form-group">
				<textarea id="message" class="form-control" rows=10 style="width: 45%"></textarea>
			</div>
			<div class="form-group">
				<button id="publish" class="btn btn-success btn-lg">Publish!</button>
			</div>
		</div>
	</div>
	<script src="https://lipis.github.io/bootstrap-social/assets/js/jquery.js"></script>
	<script type="text/javascript">
	function facebookPages() {
		$.getJSON(
			'ajax.php',
			{action: 'getFBPages'},
			function(pages) {
				var fbpages = $('select#fbpages');
				fbpages.html('');
				for(var i = 0;i < pages.length;i ++) {
					var page = pages[i];
					fbpages.append($("<option></option>")
							.attr('value', page.id)
							.text(page.name));
				}
			}
		);
	}
	$(document).ready(function() {
		window.popup = '';
		$('button.btn-social').click(function() {
			var link = $(this).data('link');
			var url = window.location.href;
			url = url.replace(/\/$/g, '');
			url += link;
			// console.log(url);
			popup = window.open(url,'_blank','width=auto,height=auto');
		});
		$('button#publish').click(function() {
			var message = $('#message').val();
			var fbpages = [];
			$('select#fbpages option:selected').each(function() {
				var pid = $(this).val();
				fbpages.push(pid);
			});
			
			$.post(
				'ajax.php',
				{
					msg: message,
					fbpages: fbpages
				},
				function(res) {
					if(res == 'ok')
						alert('Successfully published');
					else
						alert(res);
					return;
				}
			);
		});
		facebookPages();
	});
	</script>
	</body>
</html>