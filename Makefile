#!/usr/bin/env bash
.PHONY: all

all::test docs doc

test::
	bin/phpunit

docs::
	phpDocumentor.phar -d src -t docs

doc::
	$(MAKE) $(MFLAGS) -C doc

clean::
	@if test -d ./build/; then rm -rf ./build/; fi
	@find . \( -name \*.rej -o -name \*.orig -o -name .DS_Store -o -name ._\* \) -print -exec rm {} \;
