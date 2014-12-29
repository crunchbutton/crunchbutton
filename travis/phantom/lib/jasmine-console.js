jasmine.ConsoleReporter = function(printFn, doneCallback, showColors, verbose) {
  //inspired by mhevery's jasmine-node reporter
  //https://github.com/mhevery/jasmine-node

  doneCallback = doneCallback || function() {};

  var ansi = {
      green: '\033[32m',
      red: '\033[31m',
      yellow: '\033[33m',
      none: '\033[0m'
    },
    language = {
      spec: "spec",
      failure: "failure"
    };

  function coloredStr(color, str) {
    return showColors ? (ansi[color] + str + ansi.none) : str;
  }

  function greenStr(str) {
    return coloredStr("green", str);
  }

  function redStr(str) {
    return coloredStr("red", str);
  }

  function yellowStr(str) {
    return coloredStr("yellow", str);
  }

  function newline() {
    printFn("\n");
  }

  function started() {
    printFn("Started");
    newline();
  }

  function greenDot() {
    printFn(greenStr("."));
  }

  function redF() {
    printFn(redStr("F"));
  }

  function yellowStar() {
    printFn(yellowStr("*"));
  }

  function plural(str, count) {
    return count == 1 ? str : str + "s";
  }

  function repeat(thing, times) {
    var arr = [];
    for (var i = 0; i < times; i++) {
      arr.push(thing);
    }
    return arr;
  }

  function indent(str, spaces) {
    var lines = (str || '').split("\n");
    var newArr = [];
    for (var i = 0; i < lines.length; i++) {
      newArr.push(repeat(" ", spaces).join("") + lines[i]);
    }
    return newArr.join("\n");
  }

  function specFailureDetails(suiteDescription, specDescription, items) {
    newline();
    printFn(suiteDescription + " " + specDescription);
    newline();
    for (var i = 0; i < items.length; i++) {
      if (!items[i].passed()) {
        if (items[i].trace.stack)
          printFn(indent(items[i].trace.stack, 2));
        else
          printFn(indent(items[i].toString(), 2));
        newline();
      }
    }
  }

  function finished(elapsed) {
    newline();
    printFn("Finished in " + elapsed / 1000 + " seconds");
  }

  function summary(colorF, specs, failed) {
    newline();
    printFn(colorF(specs + " " + plural(language.spec, specs) + ", " +
      failed + " " + plural(language.failure, failed)));
    newline();
    newline();
  }

  function greenSummary(specs, failed) {
    summary(greenStr, specs, failed);
  }

  function redSummary(specs, failed) {
    summary(redStr, specs, failed);
  }

  function fullSuiteDescription(suite) {
    var fullDescription = suite.description;
    if (suite.parentSuite) fullDescription = fullSuiteDescription(suite.parentSuite) + " " + fullDescription;
    return fullDescription;
  }

  this.now = function() {
    return new Date().getTime();
  };

  this.reportRunnerStarting = function() {
    this.runnerStartTime = this.now();
    started();
  };

  this.reportSpecStarting = function() { /* do nothing */
  };

  this.reportSpecResults = function(spec) {
    var results = spec.results();
    if (verbose) {
      var msg;
      if (results.skipped) {
        msg = yellowStr("SKIP");
      } else if (results.passed()) {
        msg = greenStr("PASS");
      } else {
        msg = redStr("FAIL");
      }
      msg += " " + spec.getFullName();
      printFn(msg);
      newline();
    } else {
      if (results.skipped) {
        yellowStar();
      } else if (results.passed()) {
        greenDot();
      } else {
        redF();
      }
    }
  };

  this.suiteResults = [];

  this.reportSuiteResults = function(suite) {
    var suiteResult = {
      description: fullSuiteDescription(suite),
      failedSpecResults: []
    };

    suite.results().items_.forEach(function(spec) {
      if (spec.failedCount > 0 && spec.description) suiteResult.failedSpecResults.push(spec);
    });

    this.suiteResults.push(suiteResult);
  };

  function eachSpecFailure(suiteResults, callback) {
    for (var i = 0; i < suiteResults.length; i++) {
      var suiteResult = suiteResults[i];
      for (var j = 0; j < suiteResult.failedSpecResults.length; j++) {
        var failedSpecResult = suiteResult.failedSpecResults[j];
        callback(suiteResult.description, failedSpecResult.description,
                 failedSpecResult.items_);
      }
    }
  }

  this.reportRunnerResults = function(runner) {
    newline();
    eachSpecFailure(this.suiteResults, specFailureDetails);

    finished(this.now() - this.runnerStartTime);

    var results = runner.results();
    var summaryFunction = results.failedCount === 0 ? greenSummary : redSummary;
    summaryFunction(runner.specs().length, results.failedCount);
    doneCallback(runner);
  };
};