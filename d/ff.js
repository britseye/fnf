function scrollToPos(viewId)
{
   var sp = $('#'+viewId).attr("data-savedpos");
   $(window).scrollTop(sp);
}

function addNewItem(ret)
{
   var ro = $.parseJSON(ret);
   $('#working').hide();
   if (!ret)
      return;
   if (ro.success)
   {
      if (ro.mtype == 6)
      {
         suspectStatus = ro.sid;
         suspectFile == ro.iid;
      }
      $('#feedbody').prepend(ro.text);
      fixNewStatus(ro.sid);
      // Bump the getlatest ID so this does not provoke an update
      //newsfeed = parseInt(a[1]);;
   }
   else
      alert(ro.errmsg);
   $('#status').val("");
   $('#file').val("");
   $('#stpiclabel').html('Choose File');
   $('#enter_status').hide();
   $('#invite_div').show();
}

function fixNewStatus(sid)
{
   //var item = $('#postpart'+sid);
   //item.css("cursor", "pointer");
   //item.click(function(e) { feedItemClick(e, sid, false); });
   item = $('#pthumb'+sid);
   item.css("cursor", "pointer");
   item.click(function(e) { ownerClick(e, userInfo.id); });
   item = $('#ospan'+sid);
   item.css("cursor", "pointer");
   item.click(function(e) { feedItemClick(e, sid, false); });
}

function removeBadStatus(msg)
{
   if (suspectStatus == -1)
      return;
   alert(msg+"\nThe offending with a script will be removed. Please refresh");
   $('#post'+suspectStatus).remove();
   $.get("../php/zapstatus.php?sid="+suspectStatus);  // TBD remove the file also
   suspectStatus = -1;
   $('#status').val("");
   $('#file').val("");
   $('#enter_status').hide();
   $('#invite_div').show();
};

function updateLabel(n)
{
   var id, lab, hid="";;
   switch (n)
   {
      case 0:
         id = "rmug";
         lab = "rmuglabel";
         break;
      case 1:
         id = "expic";
         lab = "excflabel";
         hid = "exppic";
         break;
      case 2:
         id = "exmug";
         lab = "exmuglabel";
         hid = "exmpic"
         break;
      case 3:
         id = "stpic";
         lab = "stpiclabel";
         hid = "stpichi";
         break;
      case 4:
         id = "msgfile";
         lab = "msgfilelabel";
         //hid = "stpichi";
         break;
      default:
         return;
   }

   var cf = document.getElementById(id).value;
   var a = cf.split('.');
   var l = a[0].length;
   var short = (l > 10)? a[0].substr(l-10):a[0];
   $('#'+lab).html(short+" ?");
   $('#'+lab).attr("title", cf+" - click again to choose another.");
   if (n > 0)
      $('#'+hid).val("Y");
}

function submitNewItem()
{
   var text = $('#status_te').val();
   if (!text.length)
   {
      alert("Your post has no text");
      return false;
   }
   $('#enter_status').hide();
   $('#invite_div').show();
   $('#contribid').val(userInfo.id);
   $('#slogtype').val(1);
   $('#status_form').submit();
   $('#working').show();
   return true;
}

function busy(b)
{
   if (b)
      $('#working').show();
   else
      $('#working').hide();
}

function clearFileInput(tagId) {
    document.getElementById(tagId).innerHTML =
                    document.getElementById(tagId).innerHTML;
}

function extraSaved(rv)
{
   var ro = $.parseJSON(rv);
   if (!ro.success)
   {
      alert(ro.errmsg);
      return;
   }
   document.getElementById('extra_form').reset();
   $('#exppic').val("N");
   $('#exmpic').val("N");
   clearFileInput("expic");
   $('#excflabel').html("Choose");
   clearFileInput("exmug");
   $('#exmuglabel').html("Choose");
   $('#individual').attr("data-loaded", "N");
   switch2indi(userInfo.id);
}

function submitExtra()
{
   $('#extra_form').submit();
   return true;
}


function showEnterStatus(yes)
{
   if (yes)
   {
      $('#invite_div').hide();
      $('#selectspan').hide();
      $('#enter_status').show();
      $('#status_te').focus();
      $('#sposted').val('N');
   }
   else
   {
      $('#enter_status').hide();
      $('#invite_div').show();
      $('#selectspan').show();
   }
}

function revertDlg()
{
   $('#dlgcontainer').hide();
   $('#therest').show();
}

function setFeedQS()
{
   var months = { "jan": 0, "feb": 1, "mar": 2, "apr": 3, "may": 4, "jun": 5, "jul": 6, "aug": 7, "sep": 8, "oct": 9, "nov": 10, "dec": 11 };
   var which;
   var my;
   var month;
   var year;

   function testCheck(el, i, a)
   {
      if ($('#'+el).is(":checked"))
         which = el.substr(3);
   }

   function parseMY()
   {
      var a = my.split(" ");
      var ms = a[0];
      var ys = a[1].trim();
      if (ms.length < 3)
         return "Bad month - less than three chars.";
      ms = ms.substr(0, 3);
      ms = ms.toLowerCase();
      if (!months.hasOwnProperty(ms))
         return "Unrecognized month?";
      month = months[ms];
      if (ys.length != 4)
         return "Year is not 4 digits.";
      year = parseInt(ys);
      if (year == NaN)
         return "Year is not a number.";
      if (year < 2016 || year > 2050)
         return "Year out of range?";
      return "";
   }

   [ "fs_latest", "fs_oldest", "fs_range"].forEach(testCheck);
   if (which == "range")
   {
      $('#feedselect').attr("data-checked", "fs_range");
      my = $('#fs_monthyear').val()
      if (my == "")
      {
         alert("You selected Month/Year,\nbut did not enter a value");
         return;
      }
      var err = parseMY();
      if (err != "")
      {
         alert(err);
         return;
      }
      var t1 = new Date(year, month, 1);
      t1 = t1.getTime()/1000;
      if (month == 11) { month = 0; year++;}
      else month++;
      var t2 = new Date(year, month, 1);
      t2 = t2.getTime()/1000;
      $('#feedselect').attr("data-qs", "&rt=range&lb="+t1+"&ub="+t2);
      var t = my.substr(0,1).toUpperCase();
      t += my.substr(1);
      $('#selectspan').html(t);
      $('#feed').attr("data-feedview", "month");
   }
   else if (which == "latest")
   {
      $('#feedselect').attr("data-checked", "fs_latest");
      $('#feedselect').attr("data-qs", "&rt=latest");
      $('#selectspan').html("Latest");
      $('#feed').attr("data-feedview", "latest");
   }
   else
   {
      $('#feedselect').attr("data-checked", "fs_oldest");
      $('#feedselect').attr("data-qs", "&rt=oldest");
      $('#selectspan').html("Oldest");
      $('#feed').attr("data-feedview", "oldest");
   }
   $('#feed').attr("data-latest", "-1");
   switch2feed();
}

function doAddComment()
{
   var text = commentContext.text;
   text = $.trim(text);
   commentContext.text = text;

   var url = "../php/nfcomment.php?sid="+commentContext.id+"&logtype="+userInfo.logType+"&id="+userInfo.id+"&text="+encodeURIComponent(text);
   busy(true);;
   $.get(url, function(msg) {
      var ro = $.parseJSON(msg);
      if (!ro.success)
      {
         alert(ro.errmsg);
         busy(false);;
         return;
      }
      var cid = ro.cid;
      var s = ro.text;
      commentContext.lastp.before(s);
      var it = $('#cthumb'+cid);
      it.css("cursor", "pointer");
      it.click(function(e) { ownerClick(e, userInfo.id); });
      it = $('#ocspan'+cid);
      it.css("cursor", "pointer");
      it.click(function(e) { feedItemClick(e, cid, true); });
      busy(false);
      // Restore sanity
      switch2feed();
   });
}

function handleComment(how)
{
   if (how) {
      commentContext.text = $('#ecta').val();
      doAddComment();
   }
   else
   {
      switch2feed();
   }
   $('#ecta').val("");
}

function addComment(itemId)
{
   commentContext.id = itemId;
   //sid = itemId;
   itemIdx = "#post"+itemId;
   commentContext.lastp = $(itemIdx).find('.sentinel').last();
   switch2ecdlg();
}

function nowAsISO(){
 function pad(n){return n<10 ? '0'+n : n}
 var d = new Date();
 return '('+d.getUTCFullYear()+'-'
      + pad(d.getUTCMonth()+1)+'-'
      + pad(d.getUTCDate())+' '
      + pad(d.getUTCHours())+':'
      + pad(d.getUTCMinutes())+':'
      + pad(d.getUTCSeconds())+')'

}
function getKey()
{
   return localStorage.getItem("ff:gpkey");
}

function escB64(s)
{
   return s.replace(/\x2b/g, '%2B').replace(/\x2f/g, '%2F').replace(/\x3d/g, '%3D');
}

function addStrong(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{B BOLD TEXT HERE B}";
  te.val(t);
}

function addItalic(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{I ITALIC TEXT HERE I}";
  te.val(t);
}

function addTitle(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{T TITLE TEXT HERE T}";
  te.val(t);
}

function addLink(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{L URL_HERE LINK TEXT HERE L}";
  te.val(t);
}

function addUl(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{U\nITEM1\nITEM2\nITEM3\nU}";
  te.val(t);
}

function addOl(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{O\nITEM1\nITEM2\nITEM3\nO}";
  te.val(t);
}

function addPic(teid)
{
  var te = $('#'+teid);
  var t = te.val();
  t += "{P PIC_URL_HERE P}";
  te.val(t);
}

function markup2html(text)
{
   function doLinks(t)
   {
      var a = t.match(/{L[^}]+L}/g);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            var pos = ts.indexOf(" ");
            if (pos < 0)
               t = t.replace(a[i], "");
            else
            {
               var url = ts.substr(0,pos);
               var rest = ts.substr(pos+1);
               t = t.replace(a[i], '<a href="'+url+'">'+rest+'</a>');
            }
         }
      }
      return t;
   }

   function doList(t, ordered)
   {
      var a = ordered? t.match(/{O[^}]+O}/g): t.match(/{U[^}]+U}/g);
      if (a) {
         var s = ordered? '<ol style="margin-top: 3px;">': '<ul style="margin-top: 3px;">';
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            var b = ts.split("\n");
            for (var j = 0; j < b.length; j++)
            {
               ts = b[j].trim();
               s += "<li>"+ts+"</li>``";
            }
            s += ordered? "</ol>": "</ul>";
            t = t.replace(a[i], s);
         }
      }

      return t;
   }

   function doEmphasis(t, tbold)
   {
      var a = tbold? t.match(/{B[^}]+B}/g): t.match(/{I[^}]+I}/g);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            if (tbold)
               t = t.replace(a[i], '<b>'+ts+'</b>');
            else
               t = t.replace(a[i], '<i>'+ts+'</i>');
         }
      }
      return t;
   }

   function doImage(t)
   {
      var a = t.match(/{P[^}]+P}/g);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            t = t.replace(a[i], '<img src="'+ts+'" style="max-width:100%">');
         }
      }
      return t;
   }

   function doFill(t)
   {
      var a = t.match(/{F[^}]+F}/g);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            var n = parseInt(ts);
            if (n == NaN)
               t = t.replace(a[i], '');
            else
            {
               ts = "";
               for (var j=0; j < n; j++)
                  ts += "&nbsp;";
               t = t.replace(a[i], ts);
            }
         }
      }
      return t;
   }

   function doShim(t)
   {
      var a = t.match(/{S[^}]+S}/g);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2,l);
            ts = ts.trim();
            if (parseInt(ts) == NaN)
               t = t.replace(a[i], '');
            else
            {
               if (ts.charAt(0) == '-')
               {
                  t = t.replace(a[i], '<div style="height:0px; width:0px; margin:'+ts+'px 0 0 0;"></div>');
               }
               else if (ts == '0')
               {
                  t = t.replace(a[i], '');
               }
               else
               {
                  t = t.replace(a[i], '<div style="height:'+ts+'px; width:0px; margin:0 0 0 0;"></div>');
               }
            }
            //ts = ts.trim();
            //t = t.replace(a[i], '<div style="height:0; width:0; margin:'+ts+'px 0 0 0;"></div>');
         }
      }
      return t;
   }

   function doTable(t)
   {
      var a = t.match(/{T[^}]+T}/);
      if (a) {
         for (var i = 0; i < a.length; i++)
         {
            var l = a[i].length-4;
            var ts = a[i].substr(2, l);
            ts = ts.trim();
            var b = ts.split("\n");
            var lines = b.length;
            ts = b[0].trim();
            var c = ts.split('|');
            var cols = c.length;
            var tw = cols;
            for (var n = 0; n < cols; n++)
            {
               var m = c[n].length;
               m *= 0.8;
               c[n] = ''+m+'em;';
               tw += m;
            }
            ts = b[1];
            ts = ts.trim();
            var d = ts.split('|');
            s = '<table style="width='+tw+'em; border:solid 1px #bbbbbb; border-collapse: collapse;">'+"\n<thead>\n"+
                  '<tr style="vertical-align:top;">';
            s += '<tr>';
            for (var j = 0; j < cols; j++)
            {
               s += '<th style="width:'+c[j]+'em;" align="left">'+d[j];
            }
            s += '</tr></thead><tbody>';
            for (var j = 2; j < lines; j++)
            {
               ts = b[j].trim();
               d = ts.split('|');
               s += '<tr style="vertical-align:top;">';
               for (var k = 0; k < cols; k++)
                  s += '<td>'+d[k]+'</td>';
               s += '</tr>';
            }
            s += '</tbody></table>';
            t = t.replace(a[i], s);
         }
      }
      return t;
   }

   text = text.replace(/&/g, '&amp;');
   text = text.replace(/</g, '&lt;');
   text = text.replace(/>/g, '&gt;');
   text = text.replace(/'/g, '&apos;')
   text = text.replace(/"/g, '&quot;');
   text = text.replace(/\r/g, '');
   var a = text.match(/{H[^}]+H}/g);
   if (a)
   {
      var last = a.length-1;
      var l = a[last].length-4;
      var ts = a[last].substr(2,l);
      ts = ts.trim();
      for (var i = 0; i < a.length; i++)
         text = text.replace(a[i], "");
      text = "<h3>"+ts+"</h3>" + text;
   }

   text = doLinks(text);
   text = doEmphasis(text, true);
   text = doEmphasis(text, false);
   text = doList(text, true);
   text = doList(text, false);
   text = doImage(text);
   text = doTable(text);
   text = doShim(text);
   text = doFill(text);
   text = text.replace(/\n\n/g, '<p>');
   text = text.replace(/\n/g, '<br>');
   text = text.replace(/``/g, "\n");


   return text;
}

function makeToken(ffid, name, flags)
{
   var key = getKey();
   var hdr = '{"typ":"JWT","alg":"HS256"}';
   var now = Date / 1000 | 0;
   now += 365*24*60*60;  // in a year
   var payload = '{ "iss":"britseyeview.com/fnf/","id": '+ffid+', "name":"'+
                     name+'","flags": '+flags+', "expires": '+now+' }';
   var sig = sha256(payload+key);
   var s = btoa(hdr)+"."+btoa(payload)+"."+sig;
   return s;
}

function doRegister(ret)
{
   if (!ret)
   {
      $('#working').hide();
      $('#dlgcontainer').hide();
      $('#therest').show();

      return;
   }
   var ro = $.parseJSON(ret);
	if (!ro.success)
	{
	   alert(ro.errmsg);
      $('#working').hide();
      $('#dlgcontainer').hide();
      $('#therest').show();
	   return;
   }
   alert("The new user '"+ro.moniker+"' was successfully registered");
   $('#working').hide();
   $('#dlgcontainer').hide();
   $('#members').attr("data-loaded", "N");
   switch2members();
}

function submitRegister()
{
   var id = $('#ruserid').val();
   if (id == '')
   {
      alert("You have not entered a login name.");
      return;
   }
   id = id.toLowerCase();
   var uname = $('#rusername').val();
   if (uname == '')
   {
      alert("You have not entered a display name.");
      return;
   }
   var pwd = $('#rpass1').val();
   if (pwd == '')
   {
      alert("You have not entered a password.");
      return;
   }
   if (pwd != $('#rpass2').val())
   {
      alert("Password values don't match.");
      return;
   }
   var email = $('#remail').val();
   if (email != $('#remail2').val())
   {
      alert("Email values don't match.");
      return;
   }
   var url = '../php/register.php?moniker='+id+'&username='+uname+'&pass='+pwd+'&email='+email;
   busy(true);
   $.get(url, function(rv) { doRegister(rv); });
}

function doLogin(reply, sticky)
{
   if (!reply)
      return;
   var ro = $.parseJSON(reply);
	if (!ro.success)
	{
      alert("Login Failed:\n"+ro.errmsg);
      $('#working').hide();
      return;
	}
	var tp = ro.token;
	var ct = atob(ro.token);
	var key = getKey();
	var pt = decrypt(ct, key);
	var a = pt.split(".");
   var json = atob(a[1]);
   var obj = $.parseJSON(json);
	// Get data from token
   userInfo.logType = 1;
   userInfo.id = obj.id; // This is the numeric ID
   userInfo.name = obj.name;
   userInfo.flags = obj.flags;
	if (sticky)
	{
	   localStorage.setItem("ff:token", pt);
	   localStorage.setItem("ff:token_ct", tp);
   }
   else
   {
	   sessionStorage.setItem("ff:token", pt); // allow server calls until end of session
	   sessionStorage.setItem("ff:token_ct", tp);
   }
   userInfo.ctToken = tp;
   $('#blurb').hide();
   $('#shield').hide();
   doInitialize();
}

function submitLogin()
{
   $('#working').show();
   var sticky = $('#remember').is(':checked');
   var id = $('#userid').val().toLowerCase();
   var pass = $('#pass').val();
   var url = "../php/fflogin.php"
   var key = getKey();
   var authData = id+' '+pass;
   authData = encrypt(authData, key);
   authData = btoa(authData);
   $.ajax({
      url: url,
      beforeSend: function(xhr) {
         xhr.setRequestHeader("FF_Login", authData); // Becomes header HTTP_FF_LOGIN
      },
      success: function(data) { doLogin(data, sticky); }
   });
}

function doLogout()
{
   clearInterval(interval1);
   $('.yhmess').hide();
   $.get('../php/fflogout.php?uid='+userInfo.id);
   userInfo = {
      logType: 0,
      id: -1,
      name: "",
      thumbURL: "",
      flags: 0,
      ctToken: "",
      oid: -1,      // for messages
      oname: ""
   };
   localStorage.removeItem("ff:token");
   sessionStorage.removeItem("ff:token");
   var appState = getAppState();
   appState.container = "";
   appState.view = "";
   appstate.component = "";
   appState.oid = -1;
   putAppState(appState);

   $('#loggeduser').html("");
   $('#feed').attr('data-loaded', 'N');
   $('#members').attr('data-loaded', 'N');
   $('#messages').attr('data-loaded', 'N');
   $('#messages').attr('data-uid', '-1');
   $('#individual').attr('data-loaded', 'N');
   $('#individual').attr('data-uid', '-1');
   $('#extradlg').attr('data-loaded', 'N');
   $('#threads').attr('data-loaded', 'N');
   $('#conversation').attr('data-loaded', 'N');

   $('#feedbody').html('');
   $('#memberlist').html('');
   $('#idata').html('');
   $('#themessages').html('');
   $('#threadlist').html('');
   $('#therest').hide();
   $('#blurb').show();
   $('#shield').show();
   $('#dlgcontainer').show();
   mexDlgShow("logdlg");
   $('#userid').val("");
   $('#pass').val("");
   $('#userid').focus();
}

function populateExtra(ro)
{
   $('#exid').val(userInfo.id);
   $('#exdob').val(ro.dob);
   $('#exphone').val(ro.phone);
   $('#exaddress1').val(ro.address1);
   $('#exaddress2').val(ro.address2);
   $('#excity').val(ro.city);
   $('#exregion').val(ro.region);
   $('#expostcode').val(ro.postcode);
   $('#excountry').val(ro.country);
   $('#exhru').val(ro.hru);
   $('#exemail').val(ro.email);
}

function editUData()
{
   $.get("../php/getextradata.php?uid="+userInfo.id, function(rv) {
      var ro = $.parseJSON(rv);
      if (!ro.success)
      {
         alert(ro.errmsg);
         return;
      }
      populateExtra(ro);
      $('#therest').hide();
      $('#dlgcontainer').show();
      mexDlgShow("extradlg");
   });
}

function threadClick(oid, oname)
{
   //event.stopPropagation();
   userInfo.oid = oid;
   userInfo.oname = oname;
   switch2messages("conversation", userInfo.oid);
}
/*
function fixThreadClix()
{
   function setclick()
   {
      var trigger = $(this);
      trigger.click(threadClick);
      trigger.click(function(event) {
         event.stopPropagation();
         userInfo.oid = $(this).attr("data-oid");
         userInfo.oname = $(this).attr("data-oname");
         switch2messages("conversation", userInfo.oid);
      });
   }
   $('.threaditem').each(setclick);
}
*/
function toThreads()
{
   clearMsgDlg();
   switch2messages("threads", userInfo.id);
}

function zapMessages()
{
   $('#msglist').html("");
   $('#msgfrom').html("");
   $('#msgto').html("");
}

function logMessageChange()
{
   amContext.taChanged++;
}

function setUIOnline(on)
{
   var e = $('#msgtoonline');
   if (on)
   {
      e.css('color', 'green');
      e.html('Interacting');
   }
   else
   {
      e.html('Elsewhere');
      e.css('color', '#333333');
      $('#msgtotyping').html('');
   }
}

function setUITyping(on)
{
   if (on)
      $('#msgtotyping').html(' - typing');
   else
      $('#msgtotyping').html('');
}

function handleActivity(rv)
{
//console.log(JSON.stringify(rv));
   if (!rv)
      return;
   var ro = $.parseJSON(rv);
	if (!ro.success)
	{
      alert("Failed to check other user activity:\n"+ro.errmsg);
      clearInterval(amContext.pollID);  // stop trying
      return;
	}
	if (ro.online == false)
	{
	   setUIOnline(false);
	   setUITyping(false);
	   return;
   }
   setUIOnline(true);
   if (ro.olastchange != amContext.otherTaChanged)
   {
      if (amContext.otherTaChanged > -1)
         setUITyping(true);
      amContext.otherTaChanged = ro.olastchange;
   }
   else
      setUITyping(false);  // no further typing
   if (ro.olastmsg > amContext.prevMsg)
   {
      // get new messages
      clearInterval(amContext.pollID);  // stop polling while we get new messages
      var url = '../php/msgsgetrecent.php?midlatest='
           +ro.olastmsg+'&midprevious='+amContext.prevMsg+'&uid='+userInfo.id+'&oid='+userInfo.oid+'&cuname='+userInfo.name+'&oname='+userInfo.oname;
      $.get(url, function(rv) {
         if (!rv)
         {
            // Something wrong - quit polling
            return;
         }
         var ro = $.parseJSON(rv);
         if (!ro.success)
         {
            alert("Failed to load latest messages:\n"+ro.errmsg);
            // leave polling off
            return;
         }
         $('#conv'+userInfo.oid).prepend(ro.html);
         amContext.prevMsg = ro.lastmsg;
         amContext.pollID = setInterval(checkActivity, 2000);  // resume polling
      });
   }
}

function checkActivity()
{
   $.get('../php/msgactivity.php?action=check&uid='+userInfo.id+'&oid='+userInfo.oid+'&lastc='+amContext.taChanged+'&lastm=0',
         function(rv) { handleActivity(rv); });
}

function amPolling(on)
{
   // Establish an entry in the ffactivity table
   if (on)
   {
      amContext.prevMsg = $('#conv'+userInfo.oid).attr("data-latest");
      amContext.taChanged = 0;
      amContext.otherTaChanged = -1;
      amContext.pollID = setInterval(checkActivity, 2000);
      var url= '../php/msgactivity.php?action=add&uid='+userInfo.id+'&oid=0&lastc=0&lastm='+amContext.prevMsg;
      $.get(url);
   }
   else
   {
      clearInterval(amContext.pollID);
      $.get('../php/msgactivity.php?action=zap&uid='+userInfo.id+'&oid=0&lastc=0&lastm=0');
   }
}

function moClick(mid)
{
   if (confirm("Delete this message?"))
   {
      $('#m'+mid).remove();
      $.get("../php/deletemessage.php?mid="+mid);
   }
}

function exposeDelDiv(mid, mtype, iid)
{
   var s = "Either party may delete a message,<br>do you want to delete this one?<p>\n";
   s += '<span class="clicktext" style="cursor:pointer;" onclick="expungeMessage('+mid+', '+mtype+', '+iid+')">Delete</span>';
   s += ' &nbsp;<span class="clicktext" style="cursor:pointer;" onclick="expungeCancel('+mid+')">Cancel</span>';
   var item = $('#deldiv'+mid);
   item.html(s);
   item.show();
}

function expungeCancel(mid)
{
   var item = $('#deldiv'+mid);
   item.hide();
}

function expungeMessage(mid, mtype, iid)
{
   if (!confirm("Are you sure?"))
   {
      $('#deldiv'+mid).hide();
      return;
   }
   var elem = $('#m'+mid);
   var ifn = elem.attr("data-ifn");
   elem.remove();
   var url = "../php/expungemsg.php?mid="+mid+"&mtype="+mtype+"&iid="+iid+"&ifn="+ifn;
   $.get(url, function(rv) { if (rv != "") alert(rv); });
}

function doAddMessage(rv)
{
//alert(rv);
   var ro = $.parseJSON(rv);
   if (!ro.success)
   {
      alert(ro.errmsg);
      busy(false);
      return;
   }
   var html = ro.html;
   $('#themessages').prepend(html);
   clearMsgDlg();
   busy(false);
}

function clearStatusDlg()
{
   clearFileInput("stpic");
   $('#msgformto').val('');
   $('#status_te').val('');
   $('#stpichi').val('N');
   $('#stpiclabel').html("Choose File");
}

function clearExtraDlg()
{
   clearFileInput("expic");
   clearFileInput("exmug");
   $('#exphone').val('');
   $('#exemail').val('');
   $('#exdob').val('');
   $('#exaddress1').val('');
   $('#exaddress2').val('');
   $('#excity').val('');
   $('#exregion').val('');
   $('#expostcode').val('');
   $('#excountry').val('');
   $('#exehru').val('');
   $('#excflabel').html("Choose File");
   $('#exmuglabel').html("Choose File");
}

function clearMsgDlg()
{
   clearFileInput("msgfile");
   $('#newmsg').val('');
   $('#msgfilelabel').html("Choose File");
}

function submitMessage()
{
   var ta = $('#newmsg');
   var msg = ta.val();
   if (msg == "")
   {
      alert("You have not entered any message to send.");
      return;
   }
   var mbody = escape(msg);
   var toid = $('#ml_oid').val();
   $('#msgformto').val(toid);
   $('#msgformfrom').val(userInfo.id);
   $('#msgformname').val(userInfo.name);
   $('#msgts').val(nowAsISO());
   $('#msg_form').submit();
   busy(true);
   interval1 = setInterval(checkMsgStatus, 60000);
}

function getAppState()
{
   var st = { container: "", view: "", component: "", uid: -1, oid: -1 }
   var dd = $('#appstate');
   st.container = dd.attr("data-container");
   st.view = dd.attr("data-view");
   st.component = dd.attr("data-component");
   st.oid = dd.attr("data-oid");
   return st;
}

function putAppState(st)
{
   var dd = $('#appstate');
   dd.attr("data-container", st.container);
   dd.attr("data-view", st.view);
   dd.attr("data-component", st.component);
   dd.attr("data-uid", st.uid);
   dd.attr("data-oid", st.oid);
}

function pushState(state, path)
{
   if (atInit)
      history.replaceState(state, "", path);
   else
      history.pushState(state, "", path);
   atInit= false;
}

function hideView(vn, cn)
{
   var st = $(window).scrollTop();
   if (vn == "messages" && cn == "conversation")
   {
      amPolling(false);
   }
   if (cn == "")
      $('#'+vn).attr("data-savedpos", st);
   else
      $('#'+cn).attr("data-savedpos", st);
   $('#'+vn).hide();
}

function fixMemberClix()
{
   function setUDClick()
   {
      var e = $(this);
      var uid = e.attr("data-owner");
      e.click(function(event) { event.stopPropagation(); switch2indi(uid); });
   }

   $('.showsud').each(setUDClick);
}

function refreshMembers()
{
   var members = $('#members');
   var latest = parseInt(members.attr("data-latest"));
   var url = "../php/fillmembers.php?cu="+userInfo.id+'&latest='+latest;
   $.get(url, function(rv) {
      if (!rv) return;
      var ro = $.parseJSON(rv);
      if (!ro.success) {
         alert("Failed to refresh member list\n".ro.errmsg);
         return;
      }
      if (ro.latest > latest)
      {
         $('#memberlist').prepend(ro.html);
         fixMemberClix();
      }
      members.attr("data-latest", ro.latest);
      members.attr("data-timeout", setTimeout(refreshMembers, 60000));
   });
}

function switch2members()
{
   var appState;
   var members;

   function adjust()
   {
      if (!atInit)
         hideView(appState.view, appState.component);
      if (appState.container == "dlgcontainer")
      {
         $('#'+appState.container).hide();
         $('#shield').hide();
      }
      $('#members').show();
      scrollToPos("members");
      appState.container = "therest";
      appState.view = "members";
      appState.component = "";
      pushState(appState, "?path=members");
      putAppState(appState);
      $('#whence').val("members");
      members.attr("data-timeout", setTimeout(refreshMembers, 60000));
   }

   appState = getAppState();
   if (appState.view == "members")
      return;
   members = $('#members');
   clearTimeout(parseInt(members.attr("data-timeout")));
   var latest = parseInt(members.attr('data-latest'));
   if (latest == -1)
   {
      busy(true);
      var url = "../php/fillmembers.php?cu="+userInfo.id+'&latest=-1';
      $.get(url, function(rv) {
         var ro = $.parseJSON(rv);
         if (!ro.success)
         {
            alert("Failed to load members:\n"+ro.errmsg);
            busy(false);
            return;
         }
         $('#memberlist').html(ro.html);
         fixMemberClix();
         members.attr('data-latest', ro.latest);
         $('#memberlist').show();
         busy(false);
         adjust()
      });
   }
   else
   {
      $('#memberlist').show();
      adjust();
   }
}

function refreshThreads()
{
   var url = "/fnf/php/getmsgthreads.php?cu="+userInfo.id;
   // We'll refresh unconditionally - there's no heavy content, and this view is
   // more prone to change than any other
   $.get(url, function(rv) {
      var ro = $.parseJSON(rv);
      if (!ro.success)
      {
         alert(ro.errmsg);
         return;
      }
      $('#threadlist').html(ro.text);
      $('#threadlist').attr("data-latest", ro.latest);
      //fixThreadClix();
      $('#threads').attr("data-timeout", setTimeout(refreshThreads, 60000));
   });
}

function msgThunk(uid)
{
   var component = "threads";
   if (uid != userInfo.id)  // pick up the 'other' info
   {
      var uel = $('#user'+uid);
      userInfo.oid = uel.attr("data-uid");
      userInfo.oname = uel.attr("data-name");
      component = "conversation";
   }
   switch2messages(component, uid);
}

function switch2messages(component, oid)
{
   var appState;
   function adjust()
   {
      if (!atInit)
         hideView(appState.view, appState.component);
      if (appState.container == "dlgcontainer")
      {
         $('#dlgcontainer').hide();
         $('#shield').hide();
      }
      $('#messages').show();
      if (component == "threads")
      {
         $('#conversation').hide();
         $('#threads').show();
         scrollToPos("threads");
         $('#threads').attr("data-timeout", setTimeout(refreshThreads, 60000));
      }
      else
      {
         $('#threads').hide();
         $('#conversation').show();
         scrollToPos("conversation");
         amPolling(true);
      }
      appState.container = "therest";
      appState.view = "messages";
      appState.component = component;
      if (component == 'conversation')
         appState.oid = oid;
      pushState(appState, "?path=messages/"+component+"&uid="+oid);
      putAppState(appState);
   }

   appState = getAppState();
   if (component == "threads")
   {
      if (appState.view == "messages" && appState.component == "threads")
         return;
      var url = "../php/getmsgthreads.php?cu="+userInfo.id;
      busy(true);
      // We'll refresh unconditionally - there's no heavy content, and this view is
      // more prone to change than any other
      $.get(url, function(rv) {
         var ro = $.parseJSON(rv);
         if (!ro.success)
         {
            alert(ro.errmsg);
            busy(false);
            return;
         }
         $('#threadlist').html(ro.text);
         $('#threadlist').attr("data-latest", ro.latest);
         //fixThreadClix();
         busy(false);
         adjust();
      });
   }
   else
   {
      if (appState.view == "messages" && appState.component == "conversation")
         return;
      // First see if we have a cached list
      var cache = '';
      var current = parseInt($('#themessages').attr("data-current"));
      if (current > -1)  // there are cached lists
      {
         var ci = -1;
         cache = $('#themessages').attr("data-cached");
         var target = '|'+oid+'|';
         var p = cache.indexOf(target);
         if (p > -1)  // We have it cached
         {
            $('#conv'+current).hide();
            $('#conv'+oid).show();
            adjust();
            return;
         }
      }
      if (current > -1)
         $('#conv'+current).hide();
      var listdiv = $('<div id="conv'+oid+'" style="display:block;" data-latest="-1"></div>');
      $('#themessages').append(listdiv);
      var url = "../php/getmsgshtml.php?cu="+userInfo.id+"&other="+oid+"&cuname="+userInfo.name;
      busy(true);
      $.get(url, function(rv) {
         var ro = $.parseJSON(rv);
         if (!ro.success)
         {
            alert("Error loading messages: "+ro.errmsg);
            $('#conv'+oid).remove();
            busy(false);
            return;
         }
         $('#conv'+oid).html(ro.html);
         $('#conv'+oid).attr("data-latest", ro.latest);
         if (cache == '')
            cache = '|'+oid+'|';
         else
            cache += ''+oid+'|';
         $('#themessages').attr("data-cached", cache);
         $('#themessages').attr("data-current", oid);
         var oname = userInfo.oname;
         $('#msgfrom').html(userInfo.name);
         $('#msgto').html(oname);
         $('#msgtoname').html(oname);
         $('#msgformto').val(oid);
         $('#msgformfrom').val(userInfo.id);
         $('#msgformname').val(userInfo.name);
         $('#conversation').attr("data-uid", oid);
         busy(false);
         adjust();
      });
   }
}

function switch2indi(uid)
{
   var appState;
   function adjust()
   {
      if (!atInit)
         hideView(appState.view, appState.component);
      if (appState.container == "dlgcontainer")
      {
         $('#'+appState.container).hide();
         $('#shield').hide();
      }
      $('#individual').show();
      scrollToPos("individual");
      appState.container = "therest";
      appState.view = "individual";
      appState.component = "";
      appState.seqNo++;
      pushState(appState, "?path=individual&uid="+uid);
      putAppState(appState);
   }

   appState = getAppState();
   if (appState.view == "individual" && appState.oid == uid)
      return;
   var loaded = $('#individual').attr("data-loaded");
   var other = parseInt($('#individual').attr("data-uid"));

   if (loaded =='N' || other != uid) // need to load/reload
   {
      busy(true);
      var url = "../php/filluserdetail.php?uid="+uid;
      $.get(url, function(rv) {
         if (!rv) return;
         var ro = $.parseJSON(rv);
         if (!ro.success) {
            alert(ro.errmsg);
            busy(false);
            return;
         }
         $('#individual').attr("data-loaded", "Y");
         $('#individual').attr("data-uid", uid)
         $('#idata').html(ro.text);
         $('#membername').html($('#hidename').val());
         if (uid == userInfo.id)
         {
            $('#canedit').show();
            //$('#canedit').click(switch2edit());
            //$('#excurrentpic').val($('#hidepicid').val());
         }
         else
            $('#canedit').hide();
         busy(false);
         adjust();
      });
   }
   else
      adjust();
}

function refreshFeed()
{
   var feed = $('#feed');
   var latest = parseInt(feed.attr("data-latest"));
//alert(latest);
   if (feed.attr("data-feedview") == "latest")  // Dont update if viewing oldest or some month
   {
      var url = "../php/updatefeed.php?uid="+userInfo.id+'&latest='+latest;
      $.get(url, function(rv) {
         if (!rv) return;
         var ro = $.parseJSON(rv);
         if (!ro.success) {
            alert("Failed to refresh newsfeed\n".ro.errmsg);
            return;
         }
         if (ro.latest > latest)
         {
            $('#feedbody').prepend(ro.html);
            fixFeedClicks(userInfo.id);
         }
         feed.attr("data-latest", ro.latest);
         feed.attr("data-timeout", setTimeout(refreshFeed,30000));
      });
   }
}

function switch2feed()
{
   var appState;
   function adjust()
   {
      if (!atInit)
         hideView(appState.view, appState.component);
      if (appState.container == "dlgcontainer")
      {
         $('#dlgcontainer').hide();
         $('#shield').hide();
      }
      $('#therest').show();
      $('#feed').show();
      appState.container = "therest";
      appState.view = "feed";
      scrollToPos("feed");
      appState.component = "";
      pushState(appState, "?path=feed");
      putAppState(appState);
      setTimeout(refreshFeed, 1000);  // Update if required
   }

   appState = getAppState();
   if (appState.view == "feed")
      return;
   var feed = $('#feed');
   clearTimeout(parseInt(feed.attr("data-timeout")));
   var latest = parseInt(feed.attr("data-latest"));
   if (latest == -1)  // Load from scratch
   {
      busy(true);
      var url = "../php/fillfeed.php?uid="+userInfo.id+$('#feedselect').attr("data-qs");
      $.get(url, function(rv) {
         $('#feedbody').html(rv);
         fixFeedClicks(userInfo.id);
         latest = parseInt($('#feedclose').attr("data-feedlast"));
         feed.attr("data-latest", latest);
         busy(false);
         adjust()
      });
   }
   else
      adjust();  // The feed will be refreshed automatically
}

function editRevert()
{
   switch2indi(userInfo.id);
}

function switch2edit()
{
   var appState;
   function adjust()
   {
      //if (!atInit)
      //   hideView(appState.view, "");
      if (appState.container == "therest")
      {
         $('#shield').show();
         $('#dlgcontainer').show();
      }
      mexDlgShow("extradlg");
      appState.container = "dlgcontainer";
      appState.view = "extradlg";
      appState.component = "";
      appState.uid = userInfo.id;
      pushState(appState, "?path=extradlg");
      putAppState(appState);
   }

   appState = getAppState();
   if (appState.view == "extradlg")
      return;
   var url = "../php/getextradata.php?uid="+userInfo.id;
   busy(true);
   $.get(url, function(rv) {
      var ro = $.parseJSON(rv);
      if (!ro.success)
      {
         alert(ro.errmsg);
         return;
      }
      populateExtra(ro);
      busy(false);
      adjust();
   });
}

function switch2register()
{
   var appState = getAppState();
   if (appState.view == "regdlg")
      return;
   $('#regdlg').clearForm();
   $('#regsponsor').val(userInfo.id);
   //if (!atInit)
   //   hideView(appState.view, "");
   if (appState.container != "dlgcontainer")
   {
      $('#shield').show();
      $('#dlgcontainer').show();
   }
   mexDlgShow("regdlg");
   appState.container = "dlgcontainer";
   appState.view = "regdlg";
   appState.component = "";
   appState.uid = userInfo.id;
   pushState(appState, "?path=regdlg");
   putAppState(appState);
}

function switch2feedselect()
{
   var appState = getAppState();
   if (appState.view == "feedselect")
      return;
   var rbid = $('#feedselect').attr("data-checked");
      document.getElementById(rbid).checked = true;

   //if (!atInit)
   //   hideView(appState.view, "");
   if (appState.container != "dlgcontainer")
   {
      $('#shield').show();
      $('#dlgcontainer').show();
   }
   mexDlgShow("feedselect");
   appState.container = "dlgcontainer";
   appState.view = "feedselect";
   appState.component = "";
   appState.uid = -1;
      pushState(appState, "?path=feedselect");
   putAppState(appState);

}

function switch2udd()
{
   var appState;


   function adjust()
   {
      //if (!atInit)
      //   hideView(appState.view, "");
      if (appState.container != "dlgcontainer")
      {
         $('#shield').show();
         $('#dlgcontainer').show();
      }
      mexDlgShow("updatedlg");
      appState.container = "dlgcontainer";
      appState.view = "updatedlg";
      appState.component = "";
      appState.uid = -1;
      pushState(appState, "?path=updatedlg");
      putAppState(appState);
   }

   appState = getAppState();
   if (appState.view == "updatedlg")
      return;
   var markup;
   if (updateContext.comment)
   {
      markup = $('#cmarkup'+updateContext.id).html();
      $('#statustools').hide();
      $('#commenttools').show();
   }
   else
   {
      markup = $('#markup'+updateContext.id).html();
      $('#commenttools').hide();
      $('#statustools').show();
   }
   $('#updatete').val(markup);
   adjust();
}

function switch2ecdlg()
{
   var appState = getAppState();
   if (appState.view == "ecdlg")
      return;

   //if (!atInit)
   //   hideView(appState.view, "");
   if (appState.container != "dlgcontainer")
   {
      $('#shield').show();
      $('#dlgcontainer').show();
   }
   mexDlgShow("ecdlg");
   $('#ecta').focus();
   appState.container = "dlgcontainer";
   appState.view = "ecdlg";
   appState.component = "";
   appState.uid = -1;
      pushState(appState, "?path=ecdlg");
   putAppState(appState);
}

function revert2members()
{
   $('#dlgcontainer').hide();
   $('#shield').hide();
   $('#therest').show();
   $('#members').show();
}

function parseQS(qs)
{
   var rv = { view: "", component: "", uid: -1 };
   function splitPath(part)
   {
      var a = part.split('/');
      rv.view = a[0];
      rv.component = a[1];
   }

   var parts = qs.split('&');
   for (var i = 0; i < parts.length; i++)
   {
      var pair = parts[i].split('=');
      if (pair[0] == "path")
      {
         splitPath(pair[1]);
      }
      else
      {
         var a = parts[i].split('=');
         if (a[0] == "uid")
            rv.uid = a[1];
      }
   }
   return rv;
}

function changeState(sv)
{
   if (sv === null)
   {
      return;
   }
   else if (typeof sv == "object")
   {
      appState = getAppState();
      if (appState.container != sv.container)
      {
         $('#'+appState.container).hide();
         if (appState.container == "dlgcontainer")
            $('#shield').hide();
         else
            $('#shield').show();
         $('#'+sv.container).show();
      }
      if (appState.view != sv.view)
      {
         $('#'+appState.view).hide();
         $('#'+sv.view).show();
      }
      if (appState.component != sv.component)
      {
         $('#'+appState.component).hide();
         $('#'+sv.component).show();
      }
      setTimeout(function(){ scrollToPos(sv.view); },10);
      putAppState(sv);
   }
   else
   {
      // new state must be figured out
      var qsInfo = parseQS(sv);
      var a = sv.split('&');
      switch (qsInfo.view)
      {
         case "feed":
            switch2feed();
            break;
         case "members":
            switch2members();
            break;
         case "messages":
            switch2messages(qsInfo.component, qsInfo.uid);
            break;
         case "individual":
            switch2indi(qsInfo.uid);
            break;
         case "extradlg":
            switch2edit();
            break;
         default:
            break;
      }
   }
}

// Arguments here are a class name used to identify trigger elements, and
// the ID of the div that will initially be shown.
function fixClix(triggerclass)
{
   // This is where we set the clicks up at initialization time.
   function setclick()
   {
      var trigger = $(this);
      var id = trigger.attr("data-assocdiv");
      var query = "";
      if (id == "feed" || id == "members")
         query = "path="+id;
      else if (id == "messages")
         query = "path=messages/threads&uid=-1";

      trigger.click(function(event) { event.stopPropagation(); changeState(query); });
   }

   // The very useful each() function applies setclick to each trigger element.
   $('.'+triggerclass).each(setclick);
}

function updateHandler(option)
{
   switch (option)
   {
      case 1:
         var text = $('#updatete').val();
         text = $.trim(text);
         var etext = encodeURIComponent(text);
         $('#therest').css("overflow", "hidden");
         if (!updateContext.comment)
         {
            $.get("../php/updatestatus.php?id="+updateContext.id+"&bcid="+userInfo.id+"&blurb="+etext,
               function(rv) {
                  var ro = $.parseJSON(rv);
                  if (!ro.success) {
                     alert(ro.errmsg);
                     return;
                  }
                  else
                  {
                     $('#markup'+updateContext.id).html(text);
                     $('#posttxt'+updateContext.id).html(markup2html(text));
                     switch2feed();
                  }
               });
         }
         else
         {
            $.get("../php/updatecomment.php?id="+updateContext.id+"&bcid="+userInfo.id+"&blurb="+etext,
               function(rv) {
                  var ro = $.parseJSON(rv);
                  if (!ro.success) {
                     alert(ro.errmsg);
                  }
                  else
                  {
                     $('#cmarkup'+updateContext.id).html(text);
                     $('#comspan'+updateContext.id).html(markup2html(text));
                     switch2feed();
                  }
               });
         }
         $('#updatete').text("");
         break;
      case 2:
         if (confirm("Are you sure you want to delete this post/comment?"))
         {
            if (!updateContext.comment)
            {
               $('#post'+updateContext.id).remove();
               $.get("../php/zapstatus.php?id="+userInfo.id+"&sid="+updateContext.id);
            }
            else
            {
               $('#commentdiv'+updateContext.id).remove();
               $.get("../php/zapcomment.php?id="+userInfo.id+"&cid="+updateContext.id);
            }
            switch2feed();
         }
         break;
      default:
         switch2feed();
         break;
   }
}

function fixFeedClicks(uid)
{
   function tweakItem(item, isComment)
   {
      var nid = item.attr("data-postid");
      var owner = item.attr("data-owner");
      //var it = $('#'+i);
      if (owner != uid)
         return;     // Not clickable
      item.css("cursor", "pointer");
      item.click(function(e) { feedItemClick(e, nid, isComment); });
   }

   function eachFeedItem()
   {
      var fi = $(this);
      tweakItem(fi, false)
   }

   function eachCommentItem()
   {
      var ci = $(this);
      tweakItem(ci, true)
   }

   function eachOwnerLink()
   {
      var item = $(this);
      var owner = item.attr("data-owner");
      item.css("cursor", "pointer");
      item.click(function(e) { ownerClick(e, owner); });
   }

   //$('.postpart').each(eachFeedItem);
   //$('.comment').each(eachCommentItem);
   $('.poster').each(eachFeedItem);
   $('.fithumb').each(eachOwnerLink);
   $('.commenter').each(eachCommentItem);
}

function feedItemClick(e, nid, context)
{
   savedPosition = $(window).scrollTop();
   updateContext.id = nid;
   updateContext.comment = context;

   switch2udd(nid);
}

function ownerClick(e, owner)
{
   e.stopPropagation();
   switch2indi(owner);
}

function mexDlgShow(id)
{
$('#dlgcontainer').show();
   function doit()
   {
      var dlg = $(this);
      if (dlg.attr("id") == id)
         dlg.show();
      else
         dlg.hide();
   }

   $('.mexdlg').each(doit);
}

function requestPWReset()
{
//   $.get("sendemail.php", function(rv) {
//      alert("OK, watch your email.\nYou should get a link to\na page that will allow you\nto set a new password.");
//   });
}

function checkLogin()
{
   var t = localStorage.getItem("ff:token");
   if (!t || t === null)
   {
      t = sessionStorage.getItem("ff:token");
      if (!t || t === null)
         return; // User must log in
   }
   userInfo.logType = 1;
   var a = t.split(".");
   // Parse token and get user ID and name
   $pay = a[1];
   $pay = atob($pay);
   var to = JSON.parse($pay);

   userInfo.id = to.id; // This is the numeric ID
   userInfo.name = to.name;
   userInfo.flags = to.flags;

   // Make sure we send the token with each server request
   userInfo.ctToken = btoa(encrypt(t, getKey()));
}

function doInitialize()
{
   $('#loggeduser').html("user: "+userInfo.name);
   $('#slogtype').val(userInfo.logType);
   $('#contribid').val(userInfo.id);
   $('#exppic').val("N");
   $('#exmpic').val("N");
   $('#exid').val(userInfo.id);
   $('#sdept').val(0);
   // Try to make sure a reset is a reset
   clearStatusDlg();
   clearMsgDlg();
   clearExtraDlg();

   // Make sure the encrypted token is sent with each $.ajax() call
   $.ajaxSetup({
       beforeSend: function(xhr) { xhr.setRequestHeader('FF_Token', userInfo.ctToken); }
   });
   // We may have a token, but we are not online for the purposes of messaging
   $.get("../php/markonline.php?uid="+userInfo.id);

   if (!mpFormsSetUp)
   {
      var status_options = {
         url:        '../php/status.php',
         success:    function(rv) { addNewItem(rv); },
         beforeSubmit: function(a, jf, opts) {
            if ($('#sposted').val() == "Y")
               return false;
            if ($('#status_te').val() == '') {
               alert("You have not entered any text.");
               return false;
            }
            $('#contribid').val(userInfo.id);
            $('#slogtype').val(1);
            $('#stpichi').val('N');
            busy(true);;
            return true;
         },
         resetForm: true
      };
      //$('#status_form').ajaxForm(status_options);
      $('#status_form').submit(function() {
          // submit the form
          $(this).ajaxSubmit(status_options);
          $('#sposted').val("Y");
          // return false to prevent normal browser submit and page navigation
          return false;
      });

      var extra_options = {
         url: '../php/extradata.php',
         beforeSubmit: function(a, jf, opts) { $('#working').show(); return true; },
         success:    function(rv) { extraSaved(rv); },
         resetForm: true
      };
      $('#extra_form').ajaxForm(extra_options);
      $('#extra_form').submit(function() {
          $(this).ajaxSubmit();
          return false;
      });

      var msg_options = {
         url: "../php/putmessage.php",
         beforeSubmit: function(a, jf, opts) {
            var msg = $('#newmsg').val();
            if (msg == "")
            {
               alert("You have not entered any message to send.");
               return false;
            }
            var mbody = escape(msg);
            busy(true);
            return true;
         },
         success: function(rv) { doAddMessage(rv); },
         resetForm: true
      };
      $('#msg_form').ajaxForm(msg_options);
      $('#msg_form').submit(function() {
          $(this).ajaxSubmit();
          return false;
      });
      mpFormsSetUp = true;
   }
   fixClix("trigger");
   $('#dlgcontainer').hide();
   $('#therest').show();
   var a = location.href.split("?");
   if (a.length == 1)
      switch2feed();
   else
      changeState(a[1]);
}

function initialize()
{
   $("#login_form").submit(function(event) {
      event.preventDefault();
      var isValid = true;
      // do all your validation here
      if (!isValid) return;
      submitLogin();

   });

   $('#blurb').show();
   $('#shield').show();
   checkLogin();
   if (userInfo.logType > 0)
   {
      $('#shield').hide();
      $('#blurb').hide();
      doInitialize();
   }
   else
   {
      mexDlgShow("logdlg");
   }
}
