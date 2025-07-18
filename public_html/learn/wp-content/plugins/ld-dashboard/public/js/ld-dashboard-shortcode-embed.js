"use strict";

const meetingCountDown = () => {

    if (null === document.getElementById('ldd-meeting-timer')) {
        return false;
    }
    let countDownTimer = document.getElementById('ldd-meeting-timer');
    let startTime = countDownTimer.dataset.startTime;
    let timeZone = countDownTimer.dataset.timezone;
    var localTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;


    if (localTimeZone === 'Asia/Katmandu') {
        localTimeZone = 'Asia/Kathmandu';
    }

    //Converting Timezones to locals
    let sourceTimeZone = moment(startTime).tz(timeZone).format();
    var convertedTimezone = moment(sourceTimeZone).tz(localTimeZone).format('MMM D, YYYY HH:mm:ss');
    var convertedTimezonewithoutFormat = moment(sourceTimeZone).tz(localTimeZone).format();
    var currentTime = moment().unix();
    var meetingTime = moment(convertedTimezonewithoutFormat).unix();
    var diffTime = meetingTime - currentTime;
    const second = 1000,
        minute = second * 60,
        hour = minute * 60,
        day = hour * 24;

    if (diffTime > 0) {
        var countDown = new Date(convertedTimezone).getTime();
        const x = setInterval(() => {
            const now = new Date().getTime(),
                distance = countDown - now;
            document.getElementById("ldd-timer-days").innerText = Math.floor(distance / (day)),
                document.getElementById("ldd-timer-hours").innerText = Math.floor((distance % (day)) / (hour)),
                document.getElementById("ldd-timer-minutes").innerText = Math.floor((distance % (hour)) / (minute)),
                document.getElementById("ldd-timer-seconds").innerText = Math.floor((distance % (minute)) / second);

            //do something later when date is reached
            if (distance < 0) {
                clearInterval(x);
                location.reload();
            }
        }, second);
    } else {
        // countDownTimer.remove();
    }



}



document.addEventListener("DOMContentLoaded", () => {
    meetingCountDown();
});