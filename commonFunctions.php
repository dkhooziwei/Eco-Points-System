<?php 
    function get_next_id($con, $table, $prefix, $id_name) {
        $sql = "SELECT $id_name FROM $table 
        WHERE $id_name LIKE '$prefix%'
        ORDER BY $id_name DESC
        LIMIT 1";

        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }
        
        $row_count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);

        if($row_count == 0){
            return $prefix . "001";
        }
        else {
            $prefix_length = strlen($prefix);
            $number = substr($row[$id_name], $prefix_length);
            $number = intval($number);
            $next_number = $number + 1;
            return $prefix . str_pad($next_number, 3, "0", STR_PAD_LEFT);
        }
    }

    function point_calculation($weight, $type) {
        global $con;

        $point = 0;

        $sql = "SELECT points_per_kg 
                FROM recyclable 
                WHERE recyclable_id = '$type' 
                LIMIT 1";

        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $rate = floatval($row['points_per_kg']);

            $point = $weight * $rate;
        }

        return floor($point);
    }
?>