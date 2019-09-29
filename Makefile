.PHONEY: docs
docs: docs/Differ\ API.html

.PHONEY: test
test: test-unit

.PHONEY: fixtures
fixtures:
    compare -verbose -dissimilarity-threshold 1 -fuzz 4% -metric AE -highlight-color blue a.png a.png a-a-diff.png
    convert a-a-diff.png -matte \( +clone -fuzz 1% -transparent blue \) -compose DstOut -composite a-a-diff.png
    compare -verbose -dissimilarity-threshold 1 -fuzz 4% -metric AE -highlight-color blue a.png b.png a-b-diff.png
    convert a-b-diff.png -matte \( +clone -fuzz 1% -transparent blue \) -compose DstOut -composite a-b-diff.png

.PHONEY: test-unit
test-unit:
	./vendor/bin/phpunit --coverage-php=coverage/unit.cov

.PHONEY: coverage-clover
coverage-clover:
	./vendor/bin/phpcov merge --clover=clover.xml coverage/

.PHONEY: coverage-html
coverage-html:
	./vendor/bin/phpcov merge --html=coverage/html coverage/

.PHONEY: coverage-text
coverage-text:
	./vendor/bin/phpcov merge --text coverage/

.PHONEY: clean-coverage
clean-coverage:
	rm -rf coverage/* clover.xml

docs/Differ\ API.html: docs/Differ\ API.apib
	docker run -ti --rm -v $(PWD):/docs humangeo/aglio --theme-template triple -i docs/Differ\ API.apib -o docs/Differ\ API.html

.PHONEY: clean
clean: clean-coverage
	rm -rf docs/Differ\ API.html

.PHONEY: watch-test-unit
watch-test-unit:
	while true; do \
	  find . \( -name .git -o -name vendor \) -prune -o -name '#*' -o -name '*.php' -a -print | entr -cd make test-unit; \
	done
