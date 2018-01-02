<?php 
class ModelExtensionPortitImport extends Model {
    public function getListOfMarket(){
        return array_slice(scandir(dirname(DIR_APPLICATION).'/script/market/'), 2); 
    }

    public function getListOfOptions(){
        return array_slice(scandir(dirname(DIR_APPLICATION).'/script/options/'), 2); 
    }

    public function getListOfPrices(){
        return array_slice(scandir(dirname(DIR_APPLICATION).'/script/prices/'), 2); 
    }

    public function addFile($path_to_save){
        $json = array();
        if (move_uploaded_file($_FILES['file']['tmp_name'], $path_to_save)) {
			$json['success'] = 1;
		} else {
			$json['success'] = 0; 
			$json['error'] = "Possible file upload attack!";
		}
        return $json;
    }
    public function removeFile($name, $dir){
        if(file_exists(dirname(DIR_APPLICATION).'/script/'.$dir.'/'.$name)){
            unlink(dirname(DIR_APPLICATION).'/script/'.$dir.'/'.$name);
        }
    }
}