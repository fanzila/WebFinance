all: export

export: webfinance_HEAD.tar.gz

%.tar.gz: 
	cvs -d :ext:ovh.isvtec.com:/var/lib/cvs export -r `echo $@ | sed -e s/webfinance_// -e s/.tar.gz//` webfinance
	tar -zcf $@ webfinance
	rm -rf webfinance
