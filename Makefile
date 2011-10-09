all: test
	@echo Available targets: webfinance-VERSION.tar.gz

.PHONY: test;

test:
	$(MAKE) -C $@

%.tar.gz:
	rm -rf /tmp/$(@:%.tar.gz=%)
	rsync -avC \
	--exclude .svn \
	--exclude CVS \
	--exclude .cvsignore \
	--exclude 'etc/wf.conf' \
	--exclude 'htdocs/imgs/buttons/*.png' \
	. /tmp/$(@:%.tar.gz=%)
	cd /tmp && tar czf $@ $(@:%.tar.gz=%)
	rm -rf /tmp/$(@:%.tar.gz=%)
