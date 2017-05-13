function chkInput(textbox, inputtype, minlength, maxlength) {
	var inputcont = textbox.value;
	var rc = true;

//	alert(inputcont);
	if (inputcont == null || inputcont == "") {
	  if (minlength != 0) {
		  errMessage("chkInput", inputtype, minlength, maxlength);
//  	  textbox.focus();
      window.setTimeout(function(){textbox.focus();}, 0);
	    return false;
	  }
	  if (inputtype == "posint" || inputtype == "nagint" || inputtype == "integer" || inputtype == "float" || inputtype == "number") {
	    textbox.value = 0;
	  }
	  if (inputtype == "money") {
	    textbox.value = "0.00";
	  }
	  if (inputtype == "rate") {
	    textbox.value = "100";
	  }
	  if (inputtype == "date") {
	    textbox.value = "0000-00-00";
	  }
	  if (inputtype == "time") {
	    textbox.value = "00:00:00";
	  }
	  if (inputtype == "datetime") {
	    textbox.value = "0000-00-00 00:00:00";
	  }
		return true;
	}
	inputlength = getLength(inputcont);
	if (inputlength > maxlength || inputlength < minlength) {
		errMessage("chkInput", inputtype, minlength, maxlength);
//  	textbox.focus();
    window.setTimeout(function(){textbox.focus();}, 0);
		return false;
	}
  switch (inputtype) {
		case "posint":
		case "nagint":
    case "integer":
    case "float":
    case "number":
    case "money":
    case "rate":
      rc = isNumber(inputcont, inputtype);
      break;
		case "chinese":
		case "english":
		case "chara":
		case "chinesenum":
		case "englishnum":
		case "charanum":
		case "anychara":
		case "symbol":
		case "symbolmun":
		case "chinesesym":
		case "englishsym":
		case "charasym":
		case "chisymnum":
		case "engsymnum":
		case "charasymnum":
      rc = isCharacter(strTrim(inputcont, "both"), inputtype);
      break;
    case "date":
    case "time":
    case "datetime":
      rc = isDateTime(inputcont, inputtype);
      break;
    case "phone":
      rc = isPhone(inputcont);
      break;
    case "mobile":
      rc = isMobile(inputcont);
      break;
    case "email":
      rc = isEmail(inputcont);
      break;
    case "postcode":
      rc = isPostcode(inputcont);
      break;
    case "filename":
      rc = isFileName(inputcont);
      break;
    case "orderno":
      rc = isOrder(inputcont);
      break;
    default:
  }
  if (rc == false) {
  	errMessage("chkInput", inputtype, 0, 0);
//  	textbox.focus();
    window.setTimeout(function(){textbox.focus();}, 0);
  	return false;
  }
  return true;
}

function errMessage(funcname, errname, msg1, msg2) {
	var errtext = "";

	switch (funcname) {
		case "chkInput":
		  errtext += "请输入";
		  if (msg1 != 0 && msg2 == 0) {
		  	errtext += msg1 + "位以上的";
		  }
		  if (msg1 != 0 && msg2 != 0) {
		    if (msg1 != msg2) {
		      errtext += msg1 + "位到";
		    }
		  	errtext += msg2 + "位的";
		  }
		  if (msg1 == 0 && msg2 != 0) {
		  	errtext += msg2 + "位以下的";
		  }
      switch (errname) {
    		case "posint":
        	errtext += "正整数";
          break;
    		case "nagint":
        	errtext += "负整数";
          break;
        case "integer":
        	errtext += "整数";
          break;
        case "float":
         	errtext += "小数";
          break;
        case "number":
         	errtext += "数字";
          break;
        case "money":
         	errtext += "金额\n(小数点以下2位)\n例: 123.45";
          break;
        case "rate":
         	errtext += "不大于100的正整数";
          break;
    		case "chinese":
         	errtext += "汉字\n(一个汉字占3位)";
          break;
    		case "english":
         	errtext += "字母";
          break;
    		case "chara":
         	errtext += "汉字和字母\n(一个汉字占3位)";
          break;
    		case "chinesenum":
         	errtext += "汉字和数字\n(一个汉字占3位)";
          break;
    		case "englishnum":
         	errtext += "字母和数字";
          break;
    		case "charanum":
         	errtext += "汉字、字母和数字\n(一个汉字占3位)";
          break;
    		case "symbol":
         	errtext += "符号";
          break;
    		case "symbolmun":
         	errtext += "符号和数字";
          break;
		    case "chinesesym":
         	errtext += "汉字和符号\n(第一位必须是汉字，一个汉字占3位)";
          break;
		    case "englishsym":
         	errtext += "字母和符号\n(第一位必须是字母)";
          break;
		    case "charasym":
         	errtext += "汉字、字母和符号\n(第一位必须是汉字或字母，一个汉字占3位)";
          break;
    		case "chisymnum":
         	errtext += "汉字、符号和数字\n(第一位必须是汉字，一个汉字占3位)";
          break;
    		case "engsymnum":
         	errtext += "字母、符号和数字\n(第一位必须是字母)";
          break;
	    	case "charasymnum":
         	errtext += "汉字、字母、符号和数字\n(第一位必须是汉字或字母，一个汉字占3位)";
          break;
        case "date":
         	errtext += "入日期!\n格式为: yyyy-mm-dd\n例: 2001-01-01";
          break;
        case "time":
         	errtext += "时间!\n格式为: hh:mm:ss\n例: 01:01:01";
          break;
        case "datetime":
         	errtext += "日期和时间!\n格式为: yyyy-mm-dd hh:mm:ss\n例: 2001-01-01 01:01:01";
          break;
        case "phone":
         	errtext += "电话号码!\n格式为: +国家 地区 电话号码*分机\n例: +86 10 12345678*123456\n或者 010 12345678-123456";
          break;
        case "mobile":
         	errtext += "手机号码!";
          break;
        case "email":
         	errtext += "Eamil地址!\n格式为: abc.efg@hij.kmn";
          break;
        case "postcode":
         	errtext += "邮编!";
          break;
        case "filename":
         	errtext += "文件名!\n例: abc.txt";
          break;
        case "orderno":
         	errtext += "订单号!\n例: vgo12345678901234567890123456789";
          break;
        default:
      }
      break;
    case "mandInput":
      switch (errname) {
      	case "0":
          errtext = "请输入*项目";
          break;
        case "1":
          errtext = "请至少输入一个+项目";
          break;
        default:
      }
      break;
    default:
  }
  alert(errtext);
}

function getLength(str) {
  var strLength = 0;

  for (var i = 0; i < str.length; i++) {
    if(str.charCodeAt(i) > 255) {
      strLength += 3;
    } else {
      strLength += 1;
    }
  }
  return strLength;
}

function isNumber(str, tp) {
	switch (tp) {
		case "posint":
      var re = /^\d*$/;
      if (!re.test(str)) return false;
      break;
		case "nagint":
      var re = /^-\d*$/;
      if (!re.test(str)) return false;
      break;
    case "integer":
      var re = /^-?\d*$/;
      if (!re.test(str)) return false;
      break;
    case "float":
      var re = /^(-?\d+)(.\d)?$/;
      if (!re.test(str)) return false;
      break;
    case "number":
      return !isNaN(str);
      break;
    case "money":
      var re = /^\d+(.\d\d)?$/;
      if (!re.test(str)) return false;
      break;
    case "rate":
      var re = /^\d{2,3}$/;
      if (!re.test(str)) return false;
      if (Number(str) > 100) return false;
      break;
    default:
  }
  return true;
}

function isCharacter(str, tp) {
	switch (tp) {
		case "chinese":
    	var re = /^[！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "english":
    	var re = /^[a-zA-Z\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "chara":
    	var re = /^[a-zA-Z！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "chinesenum":
    	var re = /^[0-9！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "englishnum":
    	var re = /^[0-9a-zA-Z\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "charanum":
    	var re = /^[0-9a-zA-Z！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]+$/i;
      if(!re.test(str)) return false;
      break;
		case "symbol":
    	var re = /^[`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/]+$/i;
      if(!re.test(str)) return false;
      break;
		case "symbolmun":
    	var re = /^[0-9`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/]+$/i;
      if(!re.test(str)) return false;
      break;
		case "chinesesym":
    	var re = /^[\u4e00-\u9fff][`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]*$/i;
      if(!re.test(str)) return false;
      break;
		case "englishsym":
    	var re = /^[a-zA-Z][`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/a-zA-Z\s]*$/i;
      if(!re.test(str)) return false;
      break;
		case "charasym":
    	var re = /^[a-zA-Z\u4e00-\u9fff][a-zA-Z！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/]*$/i;
      if(!re.test(str)) return false;
      break;
		case "chisymnum":
    	var re = /^[\u4e00-\u9fff][0-9`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]*$/i;
      if(!re.test(str)) return false;
      break;
		case "engsymnum":
    	var re = /^[a-zA-Z][0-9`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/a-zA-Z\s]*$/i;
      if(!re.test(str)) return false;
      break;
		case "charasymnum":
    	var re = /^[a-zA-Z\u4e00-\u9fff][0-9`~!@#$%^&*()\-+=|\\[\]{}:;'",.<>?\/a-zA-Z！·￥—（）［］｛｝：；”“’‘、，。？〈〉《》\u4e00-\u9fff\s]*$/i;
      if(!re.test(str)) return false;
      break;
    default:
  }
  return true;
}

function isDateTime(str, tp) {
	switch (tp) {
		case "date":
      var re = /^\d{4}-\d\d-\d\d$/;
      if (!re.test(str)) return false;
//      if (!str.match(/^\d{4}-\d\d?-\d\d?$/)) {
//      	return false;
//      }
//      var ar = str.replace(/\-0/g,"-").split("-");
//      ar = new Array(parseInt(ar[0]), parseInt(ar[1])-1, parseInt(ar[2]));
//      var d = new Date(ar[0], ar[1], ar[2]);
//      return d.getFullYear() == ar[0] && d.getMonth() == ar[1] && d.getDate() == ar[2];
      break;
    case "time":
      var re = /^\d\d:\d\d:\d\d$/;
      if (!re.test(str)) return false;
//      if (!str.match(/^\d\d?:\d\d?:\d\d?$/)) {
//      	return false;
//      }
//      var ar = str.replace(/\:0/g,":").split(":");
//      ar = new Array(parseInt(ar[0]), parseInt(ar[1]), parseInt(ar[2]));
//      var d = new Date(2008, 0, 1, ar[0], ar[1], ar[2]);
//      return d.getHours() == ar[0] && d.getMinutes() == ar[1] && d.getSeconds()==ar[2];
      break;
    case "datetime":
      var re = /^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/;
      if (!re.test(str)) return false;
//      if (!str.match(/^\d{4}-\d\d?-\d\d? \d\d?:\d\d?:\d\d?$/)) {
//      	return false;
//      }
//      var ar = str.replace(/\:/g,"-").replace(/\-0/g,"-").split("-");
//      ar = new Array(parseInt(ar[0]), parseInt(ar[1])-1, parseInt(ar[2]), parseInt(ar[3]), parseInt(ar[4]), parseInt(ar[5]));
//      var d = new Date(ar[0], ar[1], ar[2], ar[3], ar[4], ar[5]);
//      return d.getFullYear() == ar[0] && d.getMonth() == ar[1] && d.getDate() == ar[2] && d.getHours() == ar[3] && d.getMinutes() == ar[4] && d.getSeconds() == ar[5];
      break;
    default:
  }
  return true;
}

function isPhone(str){
  var re = /^\+?(\d{1,2}\s)?(\d{2,4}\s)?\d{8}([-*]\d{0,6})?$/;
  if(!re.test(str)) return false;
  return true;
}

function isMobile(str){
  var re = /^1\d{10}$/;
  if(!re.test(str)) return false;
  return true;
}

function isEmail(str) {
  var re = /^[0-9a-zA-Z]+(\.?_?-?[0-9a-zA-Z]+)*@[0-9a-zA-Z]+(\.?-?[0-9a-zA-Z]+)*\.[0-9a-zA-Z]{2,4}$/;
  if(!re.test(str)) return false;
  return true;
}

function isPostcode(str){
  var re = /^\d{6}$/;
  if(!re.test(str)) return false;
  return true;
}

function isFileName(str){
  var re = /^[^\\\/:*?<>""|]+$/;
  if(!re.test(str)) return false;
  return true;
}

function isOrder(str){
  var re = /^vgo[0-9]{29}$/;
  if(!re.test(str)) return false;
  return true;
}

function mandInput(thisform, mandpat) {
	var elemcount = thisform.elements.length;
  var mandcount = mandpat.length;
	if (elemcount != mandcount) {
	  if (elemcount == mandcount - 1) {
	    mandpat = mandpat.substr(1);
	  } else {
	    alert("强制输入不匹配:" + elemcount + "元素 |" + mandcount + "输入");
		  return false;
		}
	}
	var cursor = 0;
	for (i = 0; i < elemcount; i++) {
		if (mandpat.charAt(i) == "1" && (thisform.elements[i].type == "text" || thisform.elements[i].type == "textarea" || thisform.elements[i].type == "password")) {
			if (strTrim(thisform.elements[i].value, "both") == "") {
				errMessage("mandInput", "0", 0, 0);
//				thisform.elements[i].focus();
        window.setTimeout(function(){thisform.elements[i].focus();}, 0);
				return false;
			}
		}
		if (mandpat.charAt(i) != "0" && mandpat.charAt(i) != "1") {
			if (strTrim(thisform.elements[i].value, "both") == "") {
				cursor = mandpat.indexOf(mandpat.charAt(i));
				if (cursor == i) {
					cursor = mandpat.indexOf(mandpat.charAt(i), cursor + 1);
					while (cursor != -1) {
						if (strTrim(thisform.elements[cursor].value, "both") != "") {
							break;
						}
						cursor = mandpat.indexOf(mandpat.charAt(i), cursor + 1);
					}
					if (cursor == -1) {
    				errMessage("mandInput", "1", 0, 0);
//    				thisform.elements[i].focus();
            window.setTimeout(function(){thisform.elements[i].focus();}, 0);
    				return false;
					}
				}
			}
		}
	}
	return true;
}

//删除非显示字符
function strTrim(str, type) {
  if (type == "both") {
    return str.replace(/^\s+|\s+$/g,"");
  }
  if (type == "left") {
    return str.replace(/^\s+/g,"");
  }
  if (type == "right") {
    return str.replace(/\s+$/g,"");
  }
}