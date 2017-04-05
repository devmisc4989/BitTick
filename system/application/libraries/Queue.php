<?php
/**
* Handle queue. Not suited for parallel processing, rather for long running tasks
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Queue {
    
    // add an entry to the queue
    public function push($key,$data) {
        $mydata = json_encode($data);
        $CI = & get_instance();
        $data = array(
            'key' => $key,
            'createddate' => date('Y-m-d H:i:s'),
            'data' => $mydata,
            'status' => '0'
        );
        $CI->db->insert('queue', $data);
    }

    // get an entry to the queue
    public function pop() {
        $CI = & get_instance();
        $query = $CI->db->select('queue_id,key,data')
                ->from('queue')
                ->where('status', '0')
                ->limit(1)
                ->get();
        if ($query->num_rows() == 1) {
            $result = array();
            $result['queue_id'] = $query->row()->queue_id;
            $result['key'] = $query->row()->key;
            $mydata = json_decode($query->row()->data,true);
            if(is_array($mydata))
                $data = $mydata;
            else
                $data = false;
            $result['data'] = $data;
            $CI->db->where('queue_id', $query->row()->queue_id);
            $CI->db->update('queue', array('status'=>'1'));        
        } else {
            $result = FALSE;
        }
        return $result;
    }

    // remove an entry from the queue
    public function finalize($id) {
        $CI = & get_instance();
        $CI->db->where('queue_id', $id)
            ->delete('queue');
    }

}