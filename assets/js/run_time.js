/* filename:run_time.js */

function time_calc(Y, M, D, h, m, s) {
    var seconds = 1000;
    var minutes = seconds * 60;
    var hours = minutes * 60;
    var days = hours * 24;
    var years = days * 365;

    var today = new Date();
    var time = Date.UTC(today.getFullYear(), today.getMonth() + 1, today.getDate(), today.getHours(), today.getMinutes(), today.getSeconds()) - Date.UTC(Y, M, D, h, m, s);
    var year = Math.floor(time / years)
    var day = Math.floor((time / days) - year * 365);
    var hour = Math.floor((time - (year * 365 + day) * days) / hours);
    var minute = Math.floor((time - (year * 365 + day) * days - hour * hours) / minutes);
    var second = Math.floor((time - (year * 365 + day) * days - hour * hours - minute * minutes) / seconds);

    return { "year": year, "day": day, "hour": hour, "minute": minute, "second": second };
}

function site_run_time(Y,M,D,h,m,s)
{
    window.setInterval(function() {
        var time = time_calc(Y,M,D,h,m,s);

        document.getElementById('time_year').innerText = time.year;
        document.getElementById('time_day').innerText = time.day;
        document.getElementById('time_hour').innerText = time.hour;
        document.getElementById('time_minute').innerText = time.minute;
        document.getElementById('time_second').innerText = time.second;
    }, 1000);
}