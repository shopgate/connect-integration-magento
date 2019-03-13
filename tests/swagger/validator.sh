#!/bin/sh

# NOTE: This file is modified version taken from:
# https://github.com/amardeshbd/medium-api-specification/blob/master/api-spec_validation_test.sh

# Tests the swagger specification using online service
testOpenApiSpecValidity() {
    expectedOutput="{}"
    expectedOutputSize=${#expectedOutput}

    # Prepares the spec URL from GitHub Pull-Request (PR)
    specUrl="https://raw.githubusercontent.com/$TRAVIS_REPO_SLUG/$BRANCH/docs/swagger.yaml"
    # Now prepare the open API spec file to use the online validator service.
    validationUrl="http://online.swagger.io/validator/debug?url=$specUrl"

    echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"
    echo "Validating ENV Variables: TRAVIS_BRANCH=$TRAVIS_BRANCH, PR=$PR, BRANCH=$BRANCH"
    echo "Spec URL: ${specUrl}"
    echo "OpenAPI Specification File=$validationUrl"
    echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"

    validationOutput=$(curl $validationUrl)
    validationOutputSize=${#validationOutput}
    echo "Testing swagger validation - current output is: $validationOutput"
    echo "Expected valid size: $expectedOutputSize, current output: $validationOutputSize"
    echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"

    assertEquals "Validation failed - service unavailable or error found." $expectedOutputSize $validationOutputSize
}


# Execute shunit2 to run the tests (downloaded via `.travis.yaml`)
. shunit2-2.1.6/src/shunit2
