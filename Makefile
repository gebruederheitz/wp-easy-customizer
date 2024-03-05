# Run basic linting with prettier & phpstan
lint:
	@cd util/ \
		&& asdf exec npm i \
		&& asdf exec npm run lint
	@composer lint

# Make prettier process and fix all files in src/
prettier:
	@cd util/ \
		&& asdf exec npm i \
		&& asdf exec npx prettier -w --config ../.prettierrc ../src

# Create a tagged release to publish a new version of the package
release:  lint
	@cd util/ \
	&& asdf exec npm i \
	&& asdf exec npm run release

