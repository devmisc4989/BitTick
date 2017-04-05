


<div id="dev_toolbar" >
    <!-- style wrapped  inside div for now so ajax receives one root element only to append -->
    <style type="text/css">
        #dev_toolbar {
            background: #fff89f;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            padding: 5px 30px;
            z-index: 2000000;
        }

        #dev_toolbar.dev_toolbar_hidden{
            bottom: -60px;/* match height and padding*/
        }
        #dev_tool_title {
            font-size: 20px;
            font-weight: bold;
            color: green;
            padding: 5px 20px;
            /*float: left;*/
        }


        #dev_toolbar .dev_button{
            font-size: 14px;
            padding: 6px 10px;
            text-align: center;
            color: #FFFFFF;
            /*font-family: 'Arial';*/
            font-weight: bold;
            background: #599BB3;
            cursor: pointer;
            border-width: 0px;
            border-radius: 6px;
            box-shadow: 0px 5px 9px -5px #276873;
            text-shadow: 0px 1px 0px #3D768A;
        }
        #dev_toolbar .dev_button:hover{
            background: #3D79A1;
        }
        a.dev_button{ text-decoration: none}
        form#dev_user_form{
            background: #fff89f;
            position: absolute;
            bottom:80px;
            left:30px;
            height: 160px;
            padding: 30px;
            width:300px;
            display: none;
        }
        #dev_user_form input{
            line-height: 20px;
            width: 200px;
            border: 1px solid #cccccc;
            padding: 4px 10px;
        }
        #dev_login_button{
            display: none;
        }
        #dev_toolbar_tab{
            position: absolute;
            right: 20px;
            top: 5px;
            color: darkblue;
            height: 20px;
            width: 80px;
            text-align: right;
            padding-right: 15px;
            background: #fff89f;
            font-weight: bold;
            cursor: pointer;
        }
        #dev_toolbar.dev_toolbar_hidden #dev_toolbar_tab{
            right:0;
            top:-30px;/* match it's height*/
            width: 120px;
            height: 30px;
            padding-top: 5px;
            text-align: center;
        }

    </style>
    <button class="dev_button" id="dev_path">Path</button>
    <a href="https://blacktri-dev.de/lpc/cs" class="dev_button">Dash</a>
    <button class="dev_button" id="dev_login_button">Login</button>
    <span id="dev_tool_title">Dev Toolbar (beta)</span>
    <!--hide on opt.blacktri-->
    <span class="dev_tool_dash_buttons">
        <button class="dev_button switch_environment"  data-env_switch="tenant">Switch Tenant</button>
        <button class="dev_button  switch_environment"  data-env_switch="lang">Switch Lang</button>
        <a href="https://blacktri-dev.de/editor/dev" id="dev_editor_dev" class="dev_button">Editor Dev</a>
        <button id="dev_editor_wiz" class="dev_button" data-force_visitor="0">Editor Wiz</button>
        <button id="dev_editor_visitor" class="dev_button" data-force_visitor="1">Editor Visitor</button>
    </span>
    <button class="dev_button" id="dev_tool_opt_url" >opt.blacktri url</button>
    <!--used to set up initial login user/pass to store in localStorage-->
    <form id="dev_user_form" >
        Store User/Pass in localStorage once. After that login button will work without form display<br><br>
        User: <input id="dev_user"><br><br>
        Pass:  <input id="dev_pass"><br><br>

        <button class="dev_button" type="submit">Save and Login</button>
    </form>

    <span id="dev_toolbar_tab" data-isVisible_text="[Hide X]" data-isHidden_text="Dev Toolbar">
        [Hide X]
    </span>

</div>