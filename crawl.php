<?php 

$data = $_POST;
if ($data["action"] == "upload") {
    upload();
}
elseif ($data["action"] == "crawl") {
    crawl();
}

function crawl() {
    $product_urls = array();

    $new_file_path = $_POST["new_file_path"];
    $origin_file_name = $_POST["origin_file_name"];
    $dir_name = str_replace(".csv","",$origin_file_name);
    
    $file = fopen($new_file_path, "r");
    while(($url = fgetcsv($file)) !== FALSE){
        array_push($product_urls, $url);
    }
    fclose($file);

    $product_url_first_line = $product_urls[0];
    $selected_domain = escapeshellcmd($_POST["selected_domain"]);

    if ($selected_domain == "Choose one") {

        $result = Array('failed' => "Please select a domain");
        echo json_encode($result);
    }

    else {

        $pos = strpos($product_url_first_line[0], $selected_domain);        
        
        if ( $pos > 0 ) {
            $commands = array();
            $site_name = str_replace(".com","",$selected_domain);
            $site_name = str_replace(".cn","",$site_name);

            foreach ($product_urls as $key=>$product_url) {
                $current_time = time();
                $current_num = $key + 1;
                $command = shell_exec('/bin/bash -c "(cd /home/cool/workspace/python/scrapy ; source scrapy_env/bin/activate ; cd /home/cool/workspace/python/scrapy/cn_scraper/product-ranking/product_ranking/spiders ; scrapy crawl '.$site_name.'_shelf_urls_products -o /home/cool/workspace/python/Temp/'.$site_name.'/'.$dir_name.'/'.$site_name.$current_num.'.csv -a product_url=\"'.$product_url[0].'\" -a num_pages=9999)"');
                array_push($commands, $command);
            }

            $result = Array('success' => "Crawl completed successfully!!");
            echo json_encode($result);
        }

        else {

            $result = Array('failed' => "Invalid domain");
            echo json_encode($result);
        }
    }
}

function upload() {
    error_reporting(0);
    if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {

    //set your folder path
    $path = "/var/www/html/csv/";
    //set the valid file extensions 
    $valid_formats = array("jpg", "png", "gif", "bmp", "jpeg", "GIF", "JPG", "PNG", "doc", "txt", "docx", "pdf", "xls", "xlsx", "csv"); //add the formats you want to upload

    $name = $_FILES['myfile']['name']; //get the name of the file

    $size = $_FILES['myfile']['size']; //get the size of the file

    if (strlen($name)) { //check if the file is selected or cancelled after pressing the browse button.
        list($txt, $ext) = explode(".", $name); //extract the name and extension of the file
        if (in_array($ext, $valid_formats)) { //if the file is valid go on.
            if ($size < 2098888) { // check if the file size is more than 2 mb
                $file_name = $_POST['filename']; //get the file name
                $tmp = $_FILES['myfile']['tmp_name'];
                if (move_uploaded_file($tmp, $path . $file_name.'.'.$ext)) { //check if it the file move successfully.
                    $new_file_path = $path . $file_name.'.'.$ext;
                    $result = Array('text' => "File uploaded successfully!!", 'new_file_path' => $new_file_path, 'origin_file_name' => $name);
                    // echo "File uploaded successfully!!";
                    // echo $path . $file_name.'.'.$ext;
                    echo json_encode($result);
                } else {
                    echo "failed";
                }
            } else {
                echo "File size max 2 MB";
            }
        } else {
            echo "Invalid file format..";
        }
    } else {
        echo "Please select a file..!";
    }
    exit;
    }
}

?>
