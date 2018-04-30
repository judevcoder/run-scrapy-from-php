<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Runing Scrapy</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
 
    <!-- include bootstrap files -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script>
	  	$(function () {
	  		$('#btn').click(function () {
                $('.myprogress').css('width', '0');
                $('.msg').text('');
                var filename = $('#filename').val();
                var myfile = $('#myfile').val();
                if (filename == '' || myfile == '') {
                    alert('Please enter file name and select file');
                    return;
                }
                var formData = new FormData();
                formData.append('myfile', $('#myfile')[0].files[0]);
                formData.append('filename', filename);
                $('#btn').attr('disabled', 'disabled');
				$('.msg').text('Uploading in progress...');
				formData.append('action', 'upload');
                $.ajax({
                    url: 'crawl.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    // this part is progress bar
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                $('.myprogress').text(percentComplete + '%');
                                $('.myprogress').css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (res) {
                    	var res = JSON.parse(res);
                    	var newfilepath = res['new_file_path'];
                    	var originfilename = res['origin_file_name'];
                    	$('#new_file_path').val(newfilepath);
                    	$('#origin_file_name').val(originfilename);
                        $('.msg').text(res['text']);
                        $('#btn').removeAttr('disabled');
                        $('.crawl-form').css("display", "block");
                    }
                });
            });

	  		$(document).ajaxStart(function(){
		        $("#wait").css("display", "block");
		        $('.start-crawl').disabled = true;

		    });
		    $(document).ajaxComplete(function(){
		        $("#wait").css("display", "none");
		        $('.start-crawl').disabled = false;
		    });

	        $('#crawl').on('submit', function (e) {

	          	e.preventDefault();

				$.ajax({
	            	type: 'POST',
	            	url: 'crawl.php',
	            	data: $('form').serialize() + "&action=crawl",
	            	success: function (res) {
	            		var res = JSON.parse(res);
	            		var success_message = res["success"];
	            		var error_message = res["failed"];
	            		
	            		if (success_message) {
						    $('.modal-title').text("Success");
						    $('.modal-title').css("color", "");
						    $('.modal-title').css("color", "blue");
						    $('h3').text(success_message);
						    $("#basicModal").modal("toggle");
						}
						else if (error_message) {
							$('.modal-title').text("Failed");
							$('.modal-title').css("color", "");
							$('.modal-title').css("color", "red");
						    $('h3').text(error_message);
						    $("#basicModal").modal("toggle");
						}

	            	}
		         });
	        });
	  	});
	</script>
</head>
<body>
	<div class="container" style="margin-top: 30px;">
        <div class="row">
            <form id="myform" method="post">

                <div class="form-group">
                    <label>Enter the file name: </label>
                    <input class="form-control" type="text" id="filename" /> 
                </div>
                <div class="form-group">
                    <label>Select file: </label>
                    <input class="form-control" type="file" id="myfile" />
                </div>
                <div class="form-group">
                    <div class="progress">
                        <div class="progress-bar progress-bar-success myprogress" role="progressbar" style="width:0%">0%</div>
                    </div>

                    <div class="msg"></div>
                </div>

                <input type="button" id="btn" class="btn-success" value="Upload" />
            </form>
        </div>
    </div>

    <div class="container crawl-form" style="margin-top: 50px; display: none;">
    	<div class="row">
			<form id="crawl">
				<div class="form-group">
					<label>Select one domain: </label>
				    <select name="selected_domain" id="selected_domain">
				        <option selected="selected">Choose one</option>
				        <?php
				        // A sample domain array
				        $domains = array('taobao.com', 'tmall.com', 'jd.com', 'amazon.cn', 'dangdang.com', 'yhd.com', 'hc360.com', '1688.com', 'gome.com.cn', 'suning.com');
				    
				        // Iterating through the domain array
				        foreach($domains as $item){
				        ?>
				        <option value="<?php echo strtolower($item); ?>"><?php echo $item; ?></option>
				        <?php
				        }
				        ?>
				    </select>
				</div>
			    <input type="text" name="new_file_path" id="new_file_path" style="display: none;">
			    <input type="text" name="origin_file_name" id="origin_file_name" style="display: none;">
			    <input name="submit" type="submit" value="Start Crawling" class="btn-success start-crawl">
			</form>

			<div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:50%;left:50%;padding:2px;"><img src='https://icons8.com/preloaders/preloaders/82/Fancy%20pants.gif' width="64" height="64" /><br>Crawling..</div>
		</div>
	</div>

	<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
	  	<div class="modal-dialog">
	    	<div class="modal-content">
		      	<div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h4 class="modal-title" id="myModalLabel">Basic Modal</h4>
		      	</div>
		      	<div class="modal-body">
		        	<h3>Modal Body</h3>
		      	</div>
		      	<div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      	</div>
	    	</div>
	  	</div>
	</div>


</body>
</html>
