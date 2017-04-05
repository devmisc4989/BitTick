<?php

class cases extends CI_Model {

    function __construct() {
        parent::__construct();
        // load mysql connection
        $this->load->database();
    }

    /*
     * return an array of ab-testing-cases, filtered by according to input array $filter
     * $dofilter = false allows to return an unfiltered resultset without specifying a full set of filter parameters
     */

    function getFilteredCases($dofilter, $filter) {
        if ($dofilter) {
            // use filter values to construct an sql query
            // fragment for project type
            $filterarray = array();
            if ($filter['pt_lp']['value'])
                $filterarray[] = 'landingpage';
            if ($filter['pt_op']['value'])
                $filterarray[] = 'checkout';
            if ($filter['pt_ws']['value'])
                $filterarray[] = 'homepage';
            $pt_sql = $this->getCasefilterSqlfragment($filterarray, 'type');

            // fragment for conversion goal
            $filterarray = array();
            if ($filter['cg_o']['value'])
                $filterarray[] = 'order';
            if ($filter['cg_l']['value'])
                $filterarray[] = 'registration';
            if ($filter['cg_e']['value'])
                $filterarray[] = 'engagement';
            $cg_sql = $this->getCasefilterSqlfragment($filterarray, 'goal');
            $sql = "select clientname,type,goal FROM cases where $pt_sql and $cg_sql order by clientname asc";
        }
        else {
            $sql = "select clientname,type,goal FROM cases order by clientname asc";
        }

        //echo "sql: " . $sql;
        $res = mysql_query($sql);

        // $facets is an array containing the count of each filter attribute in the result set
        $facets = array();
        $count = 0;
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
            $this->incrementFacetValue($facets, $row['type'], 'type');
            $this->incrementFacetValue($facets, $row['goal'], 'goal');
            $count++;
        }

        // query from database which facet groups would yield more results if not filtered.
        // if so, the "More" link must be displayed
        // a facet group needs the link when a query without the corresponding fragment retrieves more results
        // project type 
        $resource = mysql_query("select count(*) FROM cases where $cg_sql limit 1");
        $rowcount = mysql_result($resource, 0, 0);
        $moreresults['pt'] = ($count < $rowcount) ? true : false;
        // conversion goal
        $resource = mysql_query("select count(*) FROM cases where $pt_sql limit 1");
        $rowcount = mysql_result($resource, 0, 0);
        $moreresults['cg'] = ($count < $rowcount) ? true : false;

        return(array('entries' => $entries, 'facets' => $facets, 'moreresults' => $moreresults));
    }

    /*
     * helper function: construct an sql fragment to be used in filtering cases
     * $values is an array that contains strings like value1,..., value_n. The fragment
     * which is constructed is of the form
     * 		and <fieldname> is in (value1,...,value_n) 
     * The function takes into account situations where the $values array is empty
     * $type indicates the type of the array: 'string'|'int' and cares for using
     * or not using ' when constructing the value list
     */

    function getCasefilterSqlfragment($values, $fieldname, $type = 'string') {
        // handle delimiter based on type
        if ($type == 'string')
            $delimiter = "'";
        else
            $delimiter = "";

        $valuestring = "$fieldname in (";

        $counter = 0;
        foreach ($values as $entry) {
            $valuestring .= $delimiter . $entry . $delimiter;
            if ($counter++ < (sizeof($values) - 1))
                $valuestring .= ",";
        }

        $valuestring .= ")";
        return $valuestring;
    }

    /*
     * Helper function: increment the count of a facet, meaning: how often a filter attribute is
     * contained in a result set.
     * The functiun takes into account that the values in the database are "speaking" (which has 
     * historical reasons) and must be mapped to the names of the attributes in the HTML (like 
     * 'pt_lp' 
     * $fc: array containing the counts
     * $value: value in the database field
     * $field: name of the database field
     */

    function incrementFacetValue(&$fc, $value, $field) {
        // define a mapping between database values and attribute names, if not done yet
        global $mapping;
        if (!isset($mapping)) {
            $mapping['type_landingpage'] = "pt_lp";
            $mapping['type_checkout'] = "pt_op";
            $mapping['type_homepage'] = "pt_ws";
            $mapping['goal_order'] = "cg_o";
            $mapping['goal_registration'] = "cg_l";
            $mapping['goal_engagement'] = "cg_e";
        }
        // now get the attribute name
        $key = $field . "_" . $value;
        $attributename = $mapping[$key];
        // increment the facet with the given attributename
        if (isset($fc[$attributename])) {
            $fc[$attributename]++;
        } else {
            $fc[$attributename] = 1;
        }
    }

}

// class end here
?>