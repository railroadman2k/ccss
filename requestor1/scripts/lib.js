function dropFile(btn){
	if(document.getElementById) {
		tr = btn;
		while (tr.tagName != 'TR') tr = tr.parentNode;
		tr.parentNode.removeChild(tr);
		checkForLast();
	}	
}
function addFile(btn){
	if(document.getElementById) {
		tr = btn;
		while (tr.tagName != 'TR') tr = tr.parentNode;
		var newTr = tr.parentNode.insertBefore(tr.cloneNode(true),tr.nextSibling);
		thisChilds = newTr.getElementsByTagName('td');
		for (var i = 0; i < thisChilds.length; i++){
			if (thisChilds[i].className == 'header') thisChilds[i].innerHTML = '';
		}
		checkForLast();
	}	
}
function checkForLast(){
	btns = document.getElementsByName('drop');
	for (i = 0; i < btns.length; i++){
		btns[i].disabled = (btns.length == 1) ? true : false;
	}
}
function preview_image(pic_src)
{
	if(pic_src.value != '')
	{
		viewer_obj = pic_src.nextSibling.nextSibling;
		if(viewer_obj.tagName == 'IMG')
		{
			viewer_obj.src = pic_src.value;
			viewer_obj.width = 100;
			viewer_obj.height = 100; 
		}
	}
}
function show_image(source, viewer){
  source_obj = document.getElementById(source);
  viewer_obj = document.getElementById(viewer);
  if(source_obj.value != '') { 
    viewer_obj.src = source_obj.value; 
    viewer_obj.width = 100; 
    viewer_obj.height = 100; 
  }
}
function select_all_checkboxs(checkboxs_name, form_id)
{
	var check_obj = document.getElementById(form_id).elements[checkboxs_name]
	if(check_obj)
	{
		if(typeof(check_obj.length) == 'undefined')
		{
			tr_obj = document.getElementById('tr' + check_obj.id)
			if(check_obj.checked)
			{
				check_obj.checked = false
				tr_obj.className = 'unselected_row'
			}else
			{
				check_obj.checked = true
				tr_obj.className = 'selected_row'
			}
		}else
		{
			for(i=0;i<check_obj.length;i++)
			{
				tr_obj = document.getElementById('tr' + check_obj[i].id)
				if(check_obj[i].checked)
				{
					check_obj[i].checked = false
					tr_obj.className = 'unselected_row'
				}else
				{
					check_obj[i].checked = true
					tr_obj.className = 'selected_row'
				}
			}
		}
	}
}

function mail_preview(mail_id)
{
	if(mail_id > 0)
	{
		window.open('mail_preview.php?mail_id=' + mail_id,'preview','resizable=yes,status=yes,toolbar=yes,location=yes,menu=yes,scrollbars=yes,width=800,height=500,top=5,left=5');	
	}else
	{
		document.getElementById('act1').value='preview';
		document.getElementById('mail_form').submit();
	}
}

function ie_insert_into_textarea(el_id, ins_str)
{
	textarr_obj = document.getElementById(el_id)
	textarr_obj.focus()
	var r=document.selection.createRange()
	r.text=ins_str+r.text
	r.select()
}
function MM_openBrWindow(theURL,winName,features)
{
  window.open(theURL,winName,features);
}
function change_tr_class(check_obj)
{
	tr_obj = document.getElementById('tr'+check_obj.id)
	if(check_obj.checked)
	{
		tr_obj.className = 'selected_row'
	}else
	{
		tr_obj.className = 'unselected_row'
	}
}
