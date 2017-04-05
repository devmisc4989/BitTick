<?php

$CI = &get_instance();
$CI->lang->load('editor');
$CI->lang->load('personalization');
$tenant = $CI->config->item('tenant');

$config['valid_if_selected'] = array(
    array(
        'value' => 'AND',
        'label' => $CI->lang->line('Perso and condition'),
    ),
    array(
        'value' => 'OR',
        'label' => $CI->lang->line('Perso or condition'),
    ),
);

$config['perso_operators'] = array(
    'EQUALITY' => array(
        array(
            'value' => 'EQUALS',
            'label' => $CI->lang->line('Perso equal'),
        ),
        array(
            'value' => 'NOT_EQUALS',
            'label' => $CI->lang->line('Perso not equal'),
        ),
    ),
    'GTLT' => array(
        array(
            'value' => 'GREATER_THAN',
            'label' => $CI->lang->line('Perso greater'),
        ),
        array(
            'value' => 'LESS_THAN',
            'label' => $CI->lang->line('Perso less'),
        ),
    ),
    'RANGE' => array(
        array(
            'value' => 'TO',
            'label' => $CI->lang->line('Perso to'),
        ),
        array(
            'value' => 'FROM',
            'label' => $CI->lang->line('Perso from'),
        ),
    )
);

// definitions of single pulldown entries for conditions
$querystring_is = array(
    'label' => $CI->lang->line('Perso querystring is'),
    'tooltip' => $CI->lang->line('Perso querystring is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'queryString',
    'defaultValue' => NULL,
    'options' => NULL,
);
$url_contains = array(
    'label' => $CI->lang->line('Perso url contains'),
    'tooltip' => $CI->lang->line('Perso url contains tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'queryString',
    'defaultValue' => NULL,
    'options' => NULL,
);
$referrer_contains = array(
    'label' => $CI->lang->line('Perso referer is'),
    'tooltip' => $CI->lang->line('Perso referer is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'alphaNumSymb1',
    'defaultValue' => NULL,
    'options' => NULL,
);
$source_is = array(
    'label' => $CI->lang->line('Perso trafficsource is'),
    'tooltip' => $CI->lang->line('Perso trafficsource is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'type_in',
            'label' => $CI->lang->line('Perso source typein'),
        ),
        array(
            'value' => 'social',
            'label' => $CI->lang->line('Perso source social'),
        ),
        array(
            'value' => 'organic_search',
            'label' => $CI->lang->line('Perso source organic'),
        ),
        array(
            'value' => 'paid_search',
            'label' => $CI->lang->line('Perso source paid'),
        ),
    ),
);
$search_is = array(
    'label' => $CI->lang->line('Perso search is'),
    'tooltip' => $CI->lang->line('Perso search is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'alphaNumBlank',
    'defaultValue' => NULL,
    'options' => NULL,
);
$targetpage_opened = array(
    'label' => $CI->lang->line('Perso targetpage opened'),
    'tooltip' => $CI->lang->line('Perso targetpage opened tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'alphaNumSymb2',
    'defaultValue' => NULL,
);
$insert_basket = array(
    'label' => $CI->lang->line('Perso goal insert'),
    'tooltip' => $CI->lang->line('Perso goal insert tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => 'PERSO_YES',
    'options' => array(
        array(
            'value' => 'PERSO_YES',
            'label' => $CI->lang->line('Perso yes'),
        ),
    ),
);
$minimum_session_time = array(
    'label' => $CI->lang->line('Perso minimum session'),
    'tooltip' => $CI->lang->line('Perso minimum session tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'positiveReal',
    'defaultValue' => NULL,
    'options' => NULL,
);
$location_is = array(
    'label' => $CI->lang->line('Perso location is'),
    'tooltip' => $CI->lang->line('Perso location is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'API',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => NULL,
);
$device_is = array(
    'label' => $CI->lang->line('Perso device is'),
    'tooltip' => $CI->lang->line('Perso device is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STR_CC_ATTR_VALUE_DEVICE_TYPE_MOBILE_PHONE',
            'label' => $CI->lang->line('Perso device mobile'),
        ),
        array(
            'value' => 'STR_CC_ATTR_VALUE_DEVICE_TYPE_TABLET',
            'label' => $CI->lang->line('Perso device tablet'),
        ),
        array(
            'value' => 'STR_CC_ATTR_VALUE_DEVICE_TYPE_DESKTOP',
            'label' => $CI->lang->line('Perso device desktop'),
        ),
        array(
            'value' => 'STR_CC_ATTR_VALUE_DEVICE_TYPE_OTHERS',
            'label' => $CI->lang->line('Perso device other'),
        ),
    ),
);
$wywy_commercial_aired = array(
    'label' => $CI->lang->line('Perso wywy commercial aired'),
    'tooltip' => $CI->lang->line('Perso wywy commercial aired tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'TEXT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => 'alphaNumSymb2',
    'defaultValue' => NULL,
    'options' => NULL,
);
$last_order_time = array(
    'label' => $CI->lang->line('Perso time lastorder'),
    'tooltip' => $CI->lang->line('Perso time lastorder tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_03',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_03'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_04',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_04'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_05',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_05'),
        ),
    ),
);
$avg_sales = array(
    'label' => $CI->lang->line('Perso avg sales'),
    'tooltip' => $CI->lang->line('Perso avg sales tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_01',
            'label' => '0-10',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_02',
            'label' => '11-20',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_03',
            'label' => '21-40',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_04',
            'label' => '41-80',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_05',
            'label' => '81-100',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_06',
            'label' => '101-200',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_07',
            'label' => '201-400',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_08',
            'label' => '401-800',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_09',
            'label' => '801-1000',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_10',
            'label' => '1001-2000',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_11',
            'label' => '2001-4000',
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_12',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_12'),
        ),
    ),
);
$is_client = array(
    'label' => $CI->lang->line('Perso visitor isclient'),
    'tooltip' => $CI->lang->line('Perso visitor isclient tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_CUSTOMER_TYPE_1',
            'label' => $CI->lang->line('Perso yes'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_CUSTOMER_TYPE_2',
            'label' => $CI->lang->line('Perso no'),
        )
    ),
);
$purchaser_type = array(
    'label' => $CI->lang->line('Perso purchaser type'),
    'tooltip' => $CI->lang->line('Perso purchaser type tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_PURCHASER_TYPE_1',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_PURCHASER_TYPE_1'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_PURCHASER_TYPE_2',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_PURCHASER_TYPE_2'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_PURCHASER_TYPE_3',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_PURCHASER_TYPE_3'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_PURCHASER_TYPE_4',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_PURCHASER_TYPE_4'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_PURCHASER_TYPE_5',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_PURCHASER_TYPE_5'),
        ),
    ),
);
$is_newsletter_subscriber = array(
    'label' => $CI->lang->line('Perso visitor newsletter'),
    'tooltip' => $CI->lang->line('Perso visitor newsletter tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_NEWSLETTER_1',
            'label' => $CI->lang->line('Perso yes'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_NEWSLETTER_2',
            'label' => $CI->lang->line('Perso no'),
        )
    ),
);
$visit_count = array(
    'label' => $CI->lang->line('Perso visit count'),
    'tooltip' => $CI->lang->line('Perso visit count tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_03',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_03'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_04',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_04'),
        ),
    ),
);
$time_between_visits = array(
    'label' => $CI->lang->line('Perso time visits'),
    'tooltip' => $CI->lang->line('Perso time visits tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_FREQUENCY_SEG_01',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_FREQUENCY_SEG_01'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_FREQUENCY_SEG_02',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_FREQUENCY_SEG_02'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_FREQUENCY_SEG_03',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_FREQUENCY_SEG_03'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_FREQUENCY_SEG_04',
            'label' => $CI->lang->line('STC_CC_ATTR_VALUE_FREQUENCY_SEG_04'),
        ),
    ),
);
$is_returning = array(
    'label' => $CI->lang->line('Perso visitor returning'),
    'tooltip' => $CI->lang->line('Perso visitor returning tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISITOR_TYPE_1',
            'label' => $CI->lang->line('Perso yes'),
        ),
        array(
            'value' => 'STC_CC_ATTR_VALUE_VISITOR_TYPE_2',
            'label' => $CI->lang->line('Perso no'),
        )
    ),
);
$device_os_is = array(
    'label' => $CI->lang->line('Perso device os is'),
    'tooltip' => $CI->lang->line('Perso device os is tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'Windows',
            'label' => 'Windows',
        ),
        array(
            'value' => 'Mac OS X',
            'label' => 'Mac OS X',
        ),
        array(
            'value' => 'Linux',
            'label' => 'Linux',
        ),
        array(
            'value' => 'iOS',
            'label' => 'iOS',
        ),
        array(
            'value' => 'Android',
            'label' => 'Android',
        ),
    ),
);
$is_holiday = array(
    'label' => $CI->lang->line('Perso is holiday'),
    'tooltip' => $CI->lang->line('Perso is holiday tt'),
    'isConfigured' => TRUE,
    'widgetType' => 'SELECT',
    'operator' => array(
        'EQUALITY'
    ),
    'defaultOperator' => 'EQUALS',
    'validate' => NULL,
    'defaultValue' => NULL,
    'options' => array(
        array(
            'value' => 'YES',
            'label' => $CI->lang->line('Perso yes'),
        ),
        array(
            'value' => 'NO',
            'label' => $CI->lang->line('Perso no'),
        )
    ),
);

// grouping of conditions in one master array 
$operators = $config['perso_operators'];
if ($tenant == 'etracker') {
    $config['condition_name_select'] = array(
        'lang' => 'EN',
        'operators' => $operators,
        'groups' => array(
            'ORIGIN' => array(
                'label' => $CI->lang->line('Perso origin'),
                'elements' => array(
                    //'rta_referrer_contains' => $referrer_contains,
                    //'medium_is' => $medium_is,
                    'referrer_contains' => $referrer_contains,
                    'url_contains' => $url_contains,
                    'source_is' => $source_is,
                    'search_is' => $search_is,
                ),
            ),
            'USER_PROFILE' => array(
                'label' => $CI->lang->line('Perso user profile'),
                'elements' => array(),
            ),
            'PURCHASE_BEHAVIOR' => array(
                'label' => $CI->lang->line('Perso purchase behavior'),
                'elements' => array(),
            ),
            'VISIT_BEHAVIOR' => array(
                'label' => $CI->lang->line('Perso visit behavior'),
                'elements' => array(
                    'targetpage_opened' => $targetpage_opened,
                    'insert_basket' => $insert_basket,
                    'minimum_session_time' => $minimum_session_time,
                ),
            ),
            'TECHNOLOGY' => array(
                'label' => $CI->lang->line('Perso technology'),
                'elements' => array()
            ),
            'LOCATION' => array(
                'label' => $CI->lang->line('Perso location'),
                'elements' => array(
                    'location_is' => $location_is
                ),
            ),
        ),
    );
} else { // for blacktri
    $config['condition_name_select'] = array(
        'lang' => 'EN',
        'operators' => $operators,
        'groups' => array(
            'ORIGIN' => array(
                'label' => $CI->lang->line('Perso origin'),
                'elements' => array(
                    'referrer_contains' => $referrer_contains,
                    'url_contains' => $url_contains,
                    'source_is' => $source_is,
                    'search_is' => $search_is,
                ),
            ),
            'VISIT_BEHAVIOR' => array(
                'label' => $CI->lang->line('Perso visit behavior'),
                'elements' => array(
                    'targetpage_opened' => $targetpage_opened,
                    'cookiebased_is_returning' => $is_returning,
                    'minimum_session_time' => $minimum_session_time
                ),
            ),
            'TECHNOLOGY' => array(
                'label' => $CI->lang->line('Perso technology'),
                'elements' => array(
                    'device_wurfl_is' => $device_is,
                    'device_os_wurfl_is' => $device_os_is
                ),
            ),
            'LOCATION' => array(
                'label' => $CI->lang->line('Perso location'),
                'elements' => array(
                    'location_is' => $location_is,
                    'is_holiday' => $is_holiday
                ),
            ),
        ),
    );
}

$config['implemented_bto_methods'] = array(); // Deprecated