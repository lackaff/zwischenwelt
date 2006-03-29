// compact menu
// by Gor@MelodyRadio.Net
// version 2006-03-27

var compactmenu_tpl_pre     ='<table cellpadding="0" cellspacing="0" border="0"><tr>';
var compactmenu_tpl_mainpre =  '<tr class="s">';
var compactmenu_tpl_rootpre =  '<tr class="r">';
var compactmenu_tpl_onin    =    '<td class="onin"></td><td class="on">';
var compactmenu_tpl_onout   =    '</td><td class="onout"></td>';
var compactmenu_tpl_offin   =    '<td class="offin"></td><td class="off">';
var compactmenu_tpl_offout  =    '</td><td class="offout"></td>';
var compactmenu_tpl_mainpost=  '</tr>';
var compactmenu_tpl_rootpost=  '</tr>';
var compactmenu_tpl_post    ='</table>';

var compactmenu_itemid;

var compactmenu_children=new Array(); // children ids of a node
var compactmenu_path=new Array();     // array of node-ids
var compactmenu_label=new Array();    // text only
var compactmenu_content=new Array();  // html string incl link

function compactmenuparse(sitemap)
{
  var compactmenu_depth=new Array();  // temp.parse : current parent node id in that level
  for (i=-1; i<10; i++)
    compactmenu_depth[i]=0;
  compactmenu_path[0]='0';
  compactmenu_path[1]='0';

  for (id in sitemap)
  {
    linenr=parseInt(id)+1;
    line=trim(sitemap[id]+' | ');
    if (line.length>0)
    {
      re_depth=/^([\- ]+)/;
      if (re_depth.test(line)==false)
      {
        depth=0;
      }
      else
      {
        re_depth.exec(line);
        depth=trim(RegExp.$1).replace(/ /g,"");
        depth=depth.length;
      }
      compactmenu_depth[depth]=linenr;
      linepart=line.split("|");
      linepart[0]=linepart[0].replace(/^[\- ]+/,"");
      if (!linepart[1])
        linepart[1]=linepart[0];
      else
        linepart[1]=trim(linepart[1]);

      compactmenu_parentlinenr=compactmenu_depth[depth-1];
      compactmenu_label[linenr]=trim(linepart[0]);
      compactmenu_content[linenr]=trim(linepart[1]);
      if (!compactmenu_children[compactmenu_parentlinenr])
        compactmenu_children[compactmenu_parentlinenr]=new Array();
      compactmenu_children[compactmenu_parentlinenr][linenr]=linenr;
      compactmenu_path[linenr]=compactmenu_path[compactmenu_parentlinenr]+','+linenr;
      compactmenu_label[linenr]  = compactmenu_label[linenr].replace(/__MENUID__/,String(linenr)); 
      compactmenu_content[linenr]= compactmenu_content[linenr].replace(/__MENUID__/,String(linenr));
      // document.write(String(linenr)+' '+String(depth)+' '+line+"\r\n");
    } // if def
  } // each line
} // compactmenparse()

function compactmenuupdate(elemid, data, highlight, root)
{
  menumain='';
  menuroot='';

  for (i in data)
  {
    menuitem=data[i];
    if (menuitem.length>0)
    {
      if (i!=highlight)
      {
        menumain+=compactmenu_tpl_offin;
        menumain+=menuitem;
        menumain+=compactmenu_tpl_offout;
        menuroot+=compactmenu_tpl_offin;
        menuroot+=compactmenu_tpl_offout;
      }
      else
      {
        menumain+=compactmenu_tpl_onin;
        menumain+=menuitem;
        menumain+=compactmenu_tpl_onout;
        menuroot+=compactmenu_tpl_onin;
        menuroot+=compactmenu_tpl_onout;
      }
    } // if data
  } // each data

  switch (root)
  {
    case 'top':
      menu=compactmenu_tpl_pre;
      menu+=compactmenu_tpl_mainpre+menumain+compactmenu_tpl_mainpost;
      menu+=compactmenu_tpl_rootpre+menuroot+compactmenu_tpl_rootpost;
      menu+=compactmenu_tpl_post;
      break;
    case 'bottom':
      menu=compactmenu_tpl_pre;
      menu+=compactmenu_tpl_rootpre+menuroot+compactmenu_tpl_rootpost;
      menu+=compactmenu_tpl_mainpre+menumain+compactmenu_tpl_mainpost;
      menu+=compactmenu_tpl_post;
      break;
    case 'none':
      menu=compactmenu_tpl_pre;
      menu+=compactmenu_tpl_mainpre+menumain+compactmenu_tpl_mainpost;
      menu+=compactmenu_tpl_post;
      break;
  }
  document.getElementById(elemid).innerHTML=menu;
} // compactmenuupdate()

function compactmenusetpage(menuitemid)
{
  //document.getElementById('compactmenutable').style.display = 'none';
  var compactmenu_data_top=new Array();
  var compactmenu_data_path=new Array();
  var compactmenu_data_bottom=new Array();

  // load top:

  for (i in compactmenu_children[0])
  {
    id=compactmenu_children[0][i];
    if (id && compactmenu_content[id])
    {
      arrayAdd(compactmenu_data_top, '<div onclick="compactmenusetpage('+String(id)+'); void(0);">'+compactmenu_content[id]+'</div>',id);
    }
  }

  t=compactmenu_path[menuitemid];
  t=t.split(",");
  if (t[0]>0)
    highlight=t[0];
  else
    highlight=t[1];

  compactmenuupdate('compactmenutop', compactmenu_data_top, highlight, 'top');

  // load path:

  path=compactmenu_path[menuitemid].split(",");
  for (i in path)
  {
    id=path[i];
    if (id && compactmenu_content[id])
    {
      //arrayAdd(compactmenu_data_path, '<div onclick="compactmenusetpage('+String(id)+'); void(0);">'+compactmenu_content[id]+'</div>',id);
      arrayAdd(compactmenu_data_path, '<div onclick="compactmenusetpage('+String(id)+'); void(0);">'+compactmenu_label[id]+'</div>',id);
    }
  }

  compactmenuupdate('compactmenupath', compactmenu_data_path, menuitemid, 'none');

  // load bottom:

  useid=menuitemid;
  if (!compactmenu_children[useid] || compactmenu_children[useid].length<1)
  {
    var t=compactmenu_path[menuitemid].split(",");
    t=t[t.length-2];
    if (t!=0) useid=t;
  }

  for (i in compactmenu_children[useid])
  {
    id=compactmenu_children[useid][i];
    if (id && compactmenu_content[id])
    {
      arrayAdd(compactmenu_data_bottom, '<div onclick="compactmenusetpage('+String(id)+'); void(0);">'+compactmenu_content[id]+'</div>',id);
    }
  }
  compactmenuupdate('compactmenubottom', compactmenu_data_bottom, menuitemid, 'bottom');
  //document.getElementById('compactmenutable').style.display = 'block';
  return true;
} // compactmenusetpage()

// -- simple functions:

function trim(str)
{
  if (!str) return '';
  return str.replace(/^\s*|\s*$/g,"");
} // trim()

function arrayAdd(myarray,myelem,myid)
{
  myarray[myid]=myelem;
} // ArrayAdd()

function displaymenunotify(myid,type) 
{ 
 if (type=='reset') 
   parent.menu.getElementById(myid).className=''; 
 else 
 { 
   cn=parent.menu.getElementById(myid).className; 
   if (cn.indexOf(type)==-1) 
     parent.menu.getElementById(myid).className+=' '+type; 
 } 
} // displaymenunotify() 

// eof
