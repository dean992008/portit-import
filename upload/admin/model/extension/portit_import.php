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

    public function getListOfDone(){
        return array_slice(scandir(dirname(DIR_APPLICATION).'/script/done/'), 2); 
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

    public function script(){
        define('PRICES_FOLDER', dirname(DIR_APPLICATION).'/script/prices');
        define('MARKETS_FOLDER', dirname(DIR_APPLICATION).'/script/market');
        define('DONE_FOLDER', dirname(DIR_APPLICATION).'/script/done');
        $stock_status = array(

            '7' => 'В наличии',
            '5' => 'Нет в наличии',
            '6' => 'Ожидание 2-3 дня',
            '8' => 'Предзаказ'

        );
        $new_status[0] = 8;
        $new_status[1] = 7;
        $idx_art = 0;
        $idx_stock = 1;
        $affect_art = array();
        $prices = array();
        if ($opendir = opendir(PRICES_FOLDER)) {            
            while (false !== ($ff = readdir($opendir))) {
                if ($ff == '.' or $ff == '..')
                    continue;
                $prices[] = $ff;                
            }
        }
        $markets = array();
        if ($opendir = opendir(MARKETS_FOLDER)) {
            while (false !== ($ff = readdir($opendir))) {
                if ($ff == '.' or $ff == '..')
                    continue;
                $markets[] = $ff;
            }
        }
        if (empty($markets)) {
            return false;
        }
        $market_file = trim($markets[0]);

        foreach ($prices as $file_price) {
            $xlsx = new SpreadsheetReader(PRICES_FOLDER.'/'.$file_price);
            $arts = array();
            foreach ($xlsx as $row) {
                $this_art = trim($row[0]);
                $this_stock = (int)$row[1];
                if (in_array($this_art, $arts))
                    continue;                
                $arts[] = $this_art;
                $query = $this->db->query('SELECT * FROM '.DB_PREFIX.'`product` WHERE `sku` = "'.mysqli_real_escape_string($mconn, $this_art).'" LIMIT 1');
                $product = $query->row;
                if (count($product) == 0)
                    continue;
                $this_new_stock = $new_status[$this_stock];
                $quantity = 900;
                if ($this_stock == 0)
                    $quantity = 0;
                $this->db->query('UPDATE '.DB_PREFIX.'`product` SET `stock_status_id` = "'.$this_new_stock.'", `quantity` = "'.$quantity.'" WHERE `product_id` = "'.$product['product_id'].'" LIMIT 1');
                $affect_art[$this_art] = $this_stock;
            }
        }
        if (empty($affect_art))
            return false;
        if (empty($market_file))
            return true;
    }
    public function generateDone() {
        define('PRICES_FOLDER', dirname(DIR_APPLICATION).'/script/prices');
        define('MARKETS_FOLDER', dirname(DIR_APPLICATION).'/script/market');
        define('DONE_FOLDER', dirname(DIR_APPLICATION).'/script/done');

        $markets = array();
        if ($opendir = opendir(MARKETS_FOLDER)) {
            while (false !== ($ff = readdir($opendir))) {
                if ($ff == '.' or $ff == '..')
                    continue;
                $markets[] = $ff;
            }
        }
        if (empty($markets)) {
            return false;
        }
        foreach ($markets as $market_file) {
            $market_file = trim($market_file);
            $market_xlsx = new SpreadsheetReader(MARKETS_FOLDER.'/'.$market_file);
            $market_header = array();
            $market_stock_id = false;
            $market_data = array();
            foreach ($market_xlsx as $row) {
                $market_header = $row;
                foreach ($market_header as $idx => $title) {
                    if ($title !== 'Stock')
                        continue;
                    $market_stock_id = $idx;
                    break;    
                }
                break;
            }
            $market_header = array_flip($market_header);
            foreach ($market_header as $title => $type) {
                $market_header[$title] = 'string';
            }
            foreach ($market_xlsx as $row) {
                $market_data[] = $row;
            }
            unset($market_data[0]);
            $market_data = array_values($market_data);
            foreach ($market_data as $key => $data) {
                $this_art = trim($data[0]);
                if (!isset($affect_art[$this_art]))
                    continue;
                $market_data[$key][$market_stock_id] = $affect_art[$this_art];
            }
            if (sizeof($market_data) == 0)
                return false;
            $done_file = $market_file;
            $done_file = trim(str_replace('.xlsx', '_done.xlsx', $done_file));
            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $market_header);
            foreach ($market_data as $key => $data) {
                $writer->writeSheetRow('Sheet1', $data);	
            }
            $writer->writeToFile(DONE_FOLDER.'/'.$done_file);
        }
        return true;
    }

    public function scriptAttribute(){
        define('OPTIONS_FOLDER', dirname(DIR_APPLICATION).'/script/options');
        define('DONE_FOLDER', dirname(DIR_APPLICATION).'/script/done');
        $idx_art = 0;
        $ofiles = array();
        if ($opendir = opendir(OPTIONS_FOLDER)) {
            while (false !== ($ff = readdir($opendir))) {
                if ($ff == '.' or $ff == '..')
                    continue;
                $ofiles[] = $ff;
            }
        }
        foreach ($ofiles as $ofile) {
            $xlsx = new SpreadsheetReader(OPTIONS_FOLDER.'/'.$ofile);
            $options = array();
            $options_ids = array();
            foreach ($xlsx as $row) {
                $options = $row;
                break;
            }
            unset($options[$idx_art]);
            foreach ($options as $option) {
                $data = $this->db->query('SELECT * FROM '.DB_PREFIX.'`attribute_description` WHERE `name` = "'.mysqli_real_escape_string($mconn, $option).'" LIMIT 1')->row;
                if (empty($data['attribute_id'])) {
                    $query = $this->db->query('INSERT INTO '.DB_PREFIX.'`attribute` SET `attribute_group_id` = "7", `sort_order` = "999"');
                    $attribute_id = $this->db->getLastId();//mysqli_insert_id($mconn);
                    $this->db->query('INSERT INTO '.DB_PREFIX.'`attribute_description` SET `attribute_id` = "'.$attribute_id.'", `language_id` = "1", `name` = "'.mysqli_real_escape_string($mconn, $option).'"');
                    $options_ids[$option] = $attribute_id;
                }
                else {
                    $options_ids[$option] = $data['attribute_id'];
                }
            }
            $opt_data = array();
            foreach ($xlsx as $row) {
                $this_art = trim($row[0]);
                $opt_data[$this_art] = $row;
            }
            unset($opt_data['Part-No']);
            $opt_data = array_values($opt_data);
            foreach ($opt_data as $data) {
                $this_art = trim($data[0]);
                $product = $this->db->query('SELECT * FROM '.DB_PREFIX.'`product` WHERE `sku` = "'.mysqli_real_escape_string($mconn, $this_art).'" LIMIT 1')->row;
                if (count($product) == 0)
                    continue;
                foreach ($options as $key => $option) {
                    if ($data[$key] == 'n/a')
                        continue;
                    $query = $this->db->query('SELECT * FROM '.DB_PREFIX.'`product_attribute` WHERE `product_id` = "'.$product['product_id'].'" AND `attribute_id` = "'.$options_ids[$option].'"')->row;
                    if (count($query) == 0) {
                        $this->db->query('INSERT INTO '.DB_PREFIX.'`product_attribute` SET `product_id` = "'.$product['product_id'].'", `attribute_id` = "'.$options_ids[$option].'", `language_id` = "1", `text` = "'.$data[$key].'"');
                    }
                    else {
                        $this->db->query('UPDATE '.DB_PREFIX.'`product_attribute` SET `text` = "'.$mconn, $data[$key].'" WHERE `product_id` = "'.$product['product_id'].'" AND `attribute_id` = "'.$options_ids[$option].'" LIMIT 1');
                    }
                }
            }
            
        }
        return true;
    }
    
    public function clearAttribute(){
        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'attribute');
        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'attribute_description');
        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'product_attribute');
        return true;
    }
}