/*
 * 
 *  function for resetting collections
 * 
*/

function restartCollections(landingid,collectionid,clientid,progress)
{
	   var path=document.getElementById('path').value;
	   $.post(path+"lpc/updatecollections/",{ landingid: landingid,collectionid:collectionid,clientid:clientid,progress:progress},
	   function(data)
	   {  
			if(data)
			{
				if(data)
				{
					disablePopup('#restartCollection');
					//location.href=path+'lpc/lcd/'+collectionid;
					//reload current page
					location.href = location.href.replace(location.search, "");
					//document.getElementById('collectiondetails').innerHTML = data;
				}
			}
		});		 
		return true;  
}

/*
 * 
 *  function for updating collection details with an ajax call
 * 
*/
function findSWF(movieName) {
	  if (navigator.appName.indexOf("Microsoft")!= -1) {
	    return window["ie_" + movieName];
	  } else {
	    return document[movieName];
	  }
	}

function updateCollections()
{
	   var collectionid=document.getElementById('collectionid').value;
	   var collectionpagename=document.getElementById('collectionpagename').value;
	   var controlpagename=document.getElementById('controlpagename').value;
	   var successpagename=document.getElementById('successpagename').value;
	   var variantpagehid=document.getElementById('variantpagehid').value;
	   var variantpagehidold = document.getElementById('variantpagehidold').value;
//inputold
	   var variantId = variantpagehid.split("_");
	   var variantpageValues='', variantpageNames='';
	   var len = variantId.length;
	   if(len!=0)
	   {
	   for (var i=0;i<len-1;i++)
	   {
		   variantpageValues=variantpageValues+document.getElementById('variantpagename'+variantId[i]).value+',';
		   variantpageNames=variantpageNames+document.getElementById('variantname'+variantId[i]).value+',';
	   }
	   }
	   var variantpagehidValues='', variantpagehidNames='';
	   var variantId = variantpagehidold.split("_");
	   var len = variantId.length;
	   if(len!=0)
	   {
		   for (var i=0;i<len-1;i++)
		   {
			   
			   variantpagehidValues=variantpagehidValues+document.getElementById('variantpageold'+variantId[i]).value+',';
			   variantpagehidNames=variantpagehidNames+document.getElementById('variantold'+variantId[i]).value+',';
		   }
	   }
	   else
	   {
		   variantpagehidValues='';
		   variantpagehidNames='';
	   }
	    var path=document.getElementById('path').value;
	    var deleteid = document.frmLandingPage.deleteid.value;
	   if(collectionpagename!='' && controlpagename !='' && successpagename !='')
	   {
		   $.post(path+"lpc/save/",{ collectionid:collectionid,collectionpagename :collectionpagename,
			   			controlpagename:controlpagename,successpagename:successpagename,totallen:len,variantpageValues :variantpageValues,variantpageNames :variantpageNames,variantpagehid:variantpagehid,variantpagehidold:variantpagehidold,variantpagehidValues:variantpagehidValues,variantpagehidNames:variantpagehidNames,deleteid:deleteid},
			   function(data)
			   {   
			   			
			   				
					if(data)
					{
					  
						disablePopup('#popupContact');
						data = data.split("||");
						document.getElementById('collectiondetails').innerHTML = data[0];
						document.getElementById('colname').innerHTML = data[1];
						tmp = findSWF("chart");
						x = tmp.reload(path+"lpc/cdchart/"+collectionid);
							
					}
				});	
	   }
	   else
	   {
		   return false;
	   }
}