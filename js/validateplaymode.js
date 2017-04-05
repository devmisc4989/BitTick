// Verifies if the client is trying to start/continue a project, if so calls "getProjectConflicts" in js/conflict_layer.js
function togglecollection(collectionid, clientid, status, target) {
    if (parseInt(status) === 1) {
        bt_conflict_layer.client_id = clientid;
        bt_conflict_layer.project_id = collectionid;
        bt_conflict_layer.reload_target = target;
        bt_conflict_layer.getProjectConflicts(true);
    } else {
        doToggleCollection(collectionid, status, clientid, target);
    }
}

// calls the corresponding method in the server via AJAX to start/stop/continue a project
function doToggleCollection(collectionid, status, clientid, target) {
    var path = document.getElementById('path').value;
    $.ajax({
        url: path + "lpc/playpausecollection/",
        type: "POST",
        data: {collectionid: collectionid, action: status, clientid: clientid},
        async: false
    });
    location.href = path + target + '/' + clientid + '/';
    return true;
}

function toggleautopilot(collectionid, clientid, status, target) {
    var path = document.getElementById('path').value;
    $.ajax({
        url: path + "lpc/toggleautopilot/",
        type: "POST",
        data: {collectionid: collectionid, action: status, clientid: clientid},
        async: false
    });
    location.href = path + target + '/' + clientid + '/';
    return true;
}

function deletecollection(collectionid, clientid) {
    var path = document.getElementById('path').value;
    $.ajax({
        url: path + "lpc/deletecollection/",
        type: "POST",
        data: {collectionid: collectionid, clientid: clientid},
        async: false
    });
    location.href = path + "lpc/cs/" + clientid + "/";
    return true;
}

function playmodedetails(playid, landingpageid, playmode, collectionid)
{
    var path = document.getElementById('path').value;
    document.getElementById('playid' + playid).innerHTML = "<img src='" + path + "images/loader.gif'>";
    $.post(path + "lpc/pauserotationslot/", {collectionid: collectionid, landingpageid: landingpageid, action: playmode},
    function (data)
    {
        if (data)
        {
            data = data.split("-");
            if (data[0] == 1)   	   	//display pause button 
            {
                $('tr#' + data[1]).removeClass('table-list inactive').addClass('table-list list-active');
                document.getElementById('playid' + playid).innerHTML = '<a class="control pause"  onclick="playmodedetails(' + playid + ',' + landingpageid + ',0,' + collectionid + ')"></a>';
            }
            else if (data[0] == 0)			//display play button
            {
                $('tr#' + data[1]).removeClass('table-list active').addClass('table-list inactive');
                document.getElementById('playid' + playid).innerHTML = '<a class="control play"  onclick="playmodedetails(' + playid + ',' + landingpageid + ',1,' + collectionid + ')"></a>';
            }
            else
            {
                //document.getElementById('errormessage').innerHTML="Deleted";
            }

        }
    });
    return true;
}
