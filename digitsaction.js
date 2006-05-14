function defineDigitsAction(mytagtype,myclassname,myoffset,enterAction)
{
  myDigits=document.getElementsByTagName(mytagtype);
  myDigitsActionID=enterAction;
  first=true;
  var myi=0;

  for (var i=0;i<myDigits.length;i++)
  {
    if (myDigits[i].className==myclassname)
    {
      myi++;
      if (myDigits[i].value.length==0) myDigits[i].value=0;
      myDigits[i].tabIndex=myoffset+myi;
      myDigits[i].onkeypress=digitsAction;
      myDigits[i].onkeydown =digitsAction;
      if (first==true)
      {
        myDigits[i].focus();
        myDigits[i].select();
        first=false;
      }
    } // if class
  } // each elem
  if (document.getElementById(myDigitsActionID))
    document.getElementById(myDigitsActionID).tabIndex=myoffset+myi+1;

} // defineDigitsAction()

function digitsAction(e)
{
  if (window.event) // IE
    mykeycode1=window.event.keyCode;
  else // NS FF
    mykeycode1=e.which;

  if (mykeycode1==39) // arrow right 'increase'
  {
    this.value=parseInt(this.value)+1;
    this.focus();
    this.select();
    return false;
  }
  if (mykeycode1==37) // arrow left 'decrease'
  {
    this.value=parseInt(this.value)-1;
    this.focus();
    this.select();
    return false;
  }
  if (mykeycode1==38) // arrow up 'previous input'
  {
    focusTabIndex(this.tabIndex-1);
    return false;
  }
  if (mykeycode1==40) // arrow down 'next input'
  {
    focusTabIndex(this.tabIndex+1);
    return false;
  }
  if (mykeycode1==13) // 'enter'
  {
    if (document.getElementById(myDigitsActionID))
      document.getElementById(myDigitsActionID).click();
    return false;
  }
  return true; // allow keystroke to bubble
} // digitaction()


function focusTabIndex(index)
{
  for(var i=myDigits.length-1;i>=0;i--)
  {
    if (myDigits[i].tabIndex==index)
    {
	  myDigits[i].focus();
      myDigits[i].select();
      return true;
    }
  }
  return false;
} // focusTabIndex()