/*
	CHROMELESS WINDOWS v.35.1 [ 8.1K ]
	Licensed under GNU LGPL (www.gnu.org)

	(c) Gabriel Suchowolski,2000 >> www.microbians.com
	Thanks to Gustavo Ponce >> www.urbanlove.org (resize addon)
*/

function chromeless(u,n,W,H,X,Y,cU,cO,cL,mU,mO,xU,xO,rU,rO,tH,tW,wB,wBs,wBG,wBGs,wNS,fSO,brd,max,min,res,tsz){
	var c=(document.all&&navigator.userAgent.indexOf("Win")!=-1)?1:0
	var v=navigator.appVersion.substring(navigator.appVersion.indexOf("MSIE ")+5,navigator.appVersion.indexOf("MSIE ")+8)
	min=(v>=5.5?min:false);
	var w=window.screen.width; var h=window.screen.height
	var W=W||w; W=(typeof(W)=='string'?Math.ceil(parseInt(W)*w/100):W); W+=(brd*2+2)*c
	var H=H||h; H=(typeof(H)=='string'?Math.ceil(parseInt(H)*h/100):H); H+=(tsz+brd+2)*c
	var X=X||Math.ceil((w-W)/2)
	var Y=Y||Math.ceil((h-H)/2)
	var s=",width="+W+",height="+H
	var CWIN=window.open(u,n,wNS+s,true)
	CWIN.moveTo(X,Y)
	CWIN.focus()
	CWIN.setURL=function(u) { if (this && !this.closed) { if (this.frames.main) this.frames.main.location.href=u; else this.location.href=u } }
	CWIN.closeIT=function() { if (this && !this.closed) this.close() }
	return CWIN
}                                                                               
                                                                               

