angular.module('z5j.filters', [])

.filter("dbtimeConvert", function () {
  return function (input, format) {
    if (!/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/.test(input)) return input;
    if (format == "MM/YY HH:MM") return input.substr(5, 11).replace(/-/, "/");
  }
})

.filter("maxString", function () {
  return function (input, maxLength) {
//    if(!input || typeof(input) != "string" || typeof(input) != "number") return input;
    if (!input || typeof(input) != "string" || input.length * 2 <= maxLength) return input;
    var realLength = 0;
    for (var i = 0; i < input.length; i++) {
      charCode = input.charCodeAt(i);
      if (charCode >= 0 && charCode <= 128) {
        realLength += 1;
      } else {
        realLength += 2;
      }
      if (realLength >= maxLength) {
        return input.substr(0, i) + " ......";
      }
    }
    return input;
  }
})

.filter("maxLength", function () {
  return function (input, maxLength) {
    if (!input || typeof(input) != "string" || input.length * 3 <= maxLength) return input;
    var realLength = 0;
    for (var i = 0; i < input.length; i++) {
      charCode = input.charCodeAt(i);
      if (charCode >= 0 && charCode <= 128) {
        realLength += 1;
      } else {
        realLength += 3;
      }
      if (realLength >= maxLength) {
        return input.substr(0, i);
      }
    }
    return input;
  }
})

.filter("trustUrl", ['$sce', function ($sce) {
  return function (recordingUrl) {
    return $sce.trustAsResourceUrl(recordingUrl);
  };
}]);