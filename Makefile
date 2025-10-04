composer-format:
	- composer format

composer-lint:
	- composer lint

code-style: composer-format composer-lint

artisan:
	vendor/bin/testbench $(filter-out $@,$(MAKECMDGOALS)) $(MAKEFLAGS)

%:
	@:


