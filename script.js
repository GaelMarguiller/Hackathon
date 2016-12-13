$(function () {

    var question = $('#question');
    var answer = $('.answers');
    var answerNatif = document.getElementsByClassName('answers')[0];
    var subButton = $('#subButton');

    var prevCommand = [];
    var commandCount = 0;
    var keyCount = 0;


    $(document).keydown(function (event) {
        if (event.which == 38 && question.is(":focus")) {
            keyCount++;
            var index = prevCommand.length - keyCount;
            if (typeof prevCommand[index] !== "undefined") {
                question.val(prevCommand[index]);
            }
        } else if (event.which == 40 && question.is(":focus")) {
            keyCount--;
            var index = prevCommand.length - keyCount;
            if (typeof prevCommand[index] !== "undefined") {
                question.val(prevCommand[index]);
            }
        }
    });

    $(document).on('submit', '#chat', function () {

        if (question.val() != '') {

            commandCount++;
            keyCount = 0;
            prevCommand[commandCount] = question.val();

            if (question.val() == 'clear') {
                answer.html('');
                question.val('');
            } else {

                question.attr({'disabled': true});
                subButton.attr({'disabled': true});
                answer.append('Moi : ' + question.val() + '<br><span class="tempAnswer">IDA : <span class="point1">•</span><span class="point2">•</span><span class="point3">•</span></span>');
                answerNatif.scrollTop = answerNatif.scrollHeight;

                var inter = setInterval(function () {
                    $('.point1').css({'color': '' + getRandomColor()});
                    $('.point2').delay(100).css({'color': '' + getRandomColor()});
                    $('.point3').delay(200).css({'color': '' + getRandomColor()});
                }, 300);

                $.ajax({
                    url: 'chat.php',
                    method: 'post',
                    data: 'question=' + question.val(),
                    success: function (data) {
                        data = JSON.parse(data);
                        clearInterval(inter);
                        $('.tempAnswer').remove();
                        question.val('');
                        question.attr({'disabled': false});
                        subButton.attr({'disabled': false});
                        question.focus();
                        answer.append('IDA : ' + data.rep + '<br>');
                        var audio = new Audio(data.url);
                        audio.play();
                        answerNatif.scrollTop = answerNatif.scrollHeight;
                    }
                });
            }
        }
        return false;
    });

    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
});