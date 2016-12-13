$(function () {
    var question = $('#question');
    var answer = $('.answers');
    var answerNatif = document.getElementsByClassName('answers')[0];

    $(document).on('submit', '#chat', function () {


        if (question.length !== 0) {

            question.attr({'disabled': true});
            answer.append('Moi : ' + question.val() + '<br><span class="tempAnswer">Bot : <span class="point1">•</span><span class="point2">•</span><span class="point3">•</span></span>');
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
                    question.focus();
                    answer.append('Bot : ' + data.rep + '<br>');
                    var audio = new Audio(data.url);
                    audio.play();
                    answerNatif.scrollTop = answerNatif.scrollHeight;
                }
            });
        }
        return false;
    });
    var check = false;
    $(document).on('click', '#rec', function () {
        if (check === false) {
            question.attr({'disabled': true});
            demarrerReconnaissanceVocale();
            check = true;
        } else {
            question.attr({'disabled': false});
            endReconnaissanceVocale();
            check = false;
        }
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
