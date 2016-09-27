//jQuery

var QUESTION_NUMBER_IN_ONE_NAV_COLUMN = 5;   //number of questions shown in one navigation bar column
var lksId = 0;
var lrId = 0;
var QrId = 0;
var lksName = '';
var lksQuestion = new Array();
var currentQuestionIndex = null;
var currentAnswers = {};
var feedbackNeeded = {};
var elapsedTime = 0;

jQuery(function() //jQuery == $, not necessarily equivalen but it works
{
  if (jQuery('#question-ui').length) //detect html element with id="question-ui" //.length is to check if any element exists (return 1 = true)
  {
    $.ajax({
      type    : "POST",
      url     : "../get_student_lks_data/",  //URL to which the request is sent //ambil soal dan pilgan
      async   : false,                       //performing sync request
      data    : {"lksId": LKS_ID },//data to send to the server
      dataType: 'json',
      success : function(response)           //function to be called if the request succeeds //with argument response (bisa diganti)
      {
        //var data = $.parseJSON(response); //takes a well-formed JSON string and returns the resulting js value ()
        var data = response.lksdata;
        lksId = data.id;
        lksName = data.name;
        lksQuestion = data.question; //all data in question array (student model)
        lrId = response.lr_id;
        // Add answers to the current answers map if we have any
        if (data.answer && data.answer.length) //answer array
        {
          for (var i = 0; i < data.answer.length; i++)
          {
            var answer = data.answer[i];
            currentAnswers[(answer.questionIndex - 1)] = answer.answerId;
          }
        }
        loadQuestion(0);
        displayQuestionUI();
        updateQuestionStates();

        //re-active the first question
        jQuery('#question_nav_' + 0).addClass('active_question');
      }
    });
  }

  if (jQuery('#exam-time-left').length && TIME_LEFT)
  {
    updateTimer();
  }
});

/**
 * Hide the loading message and display the actual exam UI
 */
function displayQuestionUI() {

  // Hide loading message
  jQuery('#loading').hide();
  // Set some info in the ui
  $('#exam-name').text(lksName);
  $('#question-count').text(lksQuestion.length);
  // build the navigation bar
  var navQuestions;
  for (var i = 0; i < lksQuestion.length; i++) {
    if (i % QUESTION_NUMBER_IN_ONE_NAV_COLUMN == 0) {
        topic        = lksQuestion[i].topic;
        var navArea  = jQuery('#navigation-area');
        var navUl    = jQuery('<ul/>');
        var navBar   = 'question-nav' + (i / QUESTION_NUMBER_IN_ONE_NAV_COLUMN + 1);
        navUl.attr('id', navBar); //set attribute id ="question-nav"
        navUl.attr('class', 'pagination'); //set attribute class="pagination"
        navArea.append(navUl); //insert new <ul> content
        var navTopic = jQuery('<li/>'); //select <li> content
        navUl.append(navTopic); //insert new <li> content
        navQuestions = jQuery('<ul/>'); //????
        navTopic.append(navQuestions); //????
    }

    //generate <a id="" href="do nothing">
    var navLink = jQuery('<a/>');
    navLink.attr('id', 'question_nav_' + i);
    navLink.attr('href', 'javascript:void(0);');
    navLink.attr('style', 'width: 15px; text-align: center;');
    navLink.attr('class', 'question_unanswered');
    navLink.text((i + 1));          //generate list of question links in navigation bar
    navLink.click(function(){
      navigateToQuestion(jQuery(this));
    });

    var answeredText = jQuery('<span/>');
    answeredText.text(' (Answered)');
    answeredText.hide();
    navLink.append(answeredText);

    var navList = jQuery('<li/>');
    navList.attr('id', 'nav-list-' + i);
    navList.append(navLink);
    navList.append(answeredText);

    navQuestions.append(navList);
  }

  // Add click event to the buttons
  jQuery('#skip-button').click(function() { skipQuestion(); });
  jQuery('#record-answer-button').click(function() { recordAnswer(); });
  jQuery('#finish-exam-button').click(function() { confirmFinish(); });

  // Show the actual UI
  jQuery('#question-ui').show();

}

/**
 * Set the proper state labels on all the questions
 */
function updateQuestionStates() {
  var topic_counter = -1;
  var topic = '';
  for (var i = 0; i < lksQuestion.length; i++) {
    if (lksQuestion[i].topic != topic) {
        topic_counter++;
        topic = lksQuestion[i].topic;
    }
    if (currentAnswers[i]) {
        jQuery('#nav-list-' + i + ' a').attr('class', 'question_answered_question');
    }
  }
}

/**
 * Hide the exam UI
 */
function hideQuestionUI() {
  jQuery('#question-ui').hide();
}

function deactiveQuestion(index) {
    // Color the active question
    jQuery('#question_nav_' + index).removeClass('active_question');
}
/**
 * Load the specified question
 */
function loadQuestion(index) {

  if (index >= lksQuestion.length)
  {
    index = 0;
  }
  currentQuestionIndex = index;

  var question = lksQuestion[currentQuestionIndex];

  // Set some info in the ui
  jQuery('#question-index').text((currentQuestionIndex + 1));
  jQuery('#topic-name').html(question.topic);
  jQuery('#question-text').html(question.text);
  jQuery('#question-id').val(question.q_id);
  jQuery('#question-count').text(lksQuestion.length);

  // Add the questions
  jQuery('#answers').empty(); //empty the content of <ul> element with id="answer"
  for (var i = 0; i < question.answer.length; i++) {

    var answer = question.answer[i];

    var li = jQuery('<li />');
    var radio = jQuery("<input type='radio' name='answer' id='" + 'answer_' + i + "' />");
    radio.val(answer.id);

    if (currentAnswers[currentQuestionIndex] && currentAnswers[currentQuestionIndex] == answer.id) {
      radio.attr('checked', 'checked');
    }

    var label = jQuery('<label />');
    label.attr('for', 'answer_' + i);
    label.html(answer.text);
    label.attr('class', 'question_choice');

    li.append(radio);
    li.append(label);
    jQuery('#answers').append(li);
  }
  jQuery('#answers').append('</ul>');

  // Color the active question
  jQuery('#question_nav_' + index).addClass('active_question');

  // Handle the skip button
  if (currentQuestionIndex == lksQuestion.length - 1) {
    jQuery('#skip-button').hide();
  } else {
    jQuery('#skip-button').show();
  }
}

// Clears the feedback checkbox
function clearFeedback() {
  if (jQuery('#register-feedback').is(':checked')) {
    jQuery('#register-feedback').removeAttr('checked');
  }
}

function skipQuestion() {
  clearFeedback();
  deactiveQuestion(currentQuestionIndex);
  loadQuestion(currentQuestionIndex + 1);
}

function navigateToQuestion(caller) {
  var callerId = caller.attr('id').replace('question_nav_', '');
  clearFeedback();
  deactiveQuestion(currentQuestionIndex);
  loadQuestion(parseInt(callerId));
}

/**
 * recordAnswer: if Submit button is shown, record answer button will only submit the answer of the question
 * if not, recordAnswer will submit the answer as well as the rating.
 */
function recordAnswer() { //dont need the student id, only need the lr_id

  // Find the checked element
  checkedElement = jQuery("#answers input[type='radio']:checked");
  if(checkedElement.length) {
    var answerId = checkedElement.val();
    currentAnswers[currentQuestionIndex] = answerId;
    jQuery.ajax({
      type: 'POST',
      url: '../save_answer.php',
      async: false,
      data: {id: lrId, q_id: jQuery('#question-id').val(), a: answerId},
      success: function(data){
        if (data != 'success'){
             showError(data);
        }
      }
    });
    updateQuestionStates();
  }
  clearFeedback();

  deactiveQuestion(currentQuestionIndex);
  // Navigate to the next question
  loadQuestion(currentQuestionIndex + 1);
}


function confirmFinish() {

  if (confirm('Anda yakin sudah selesai mengerjakan LKS ini?'))
  {
    finishLKS();
  }

}

function finishLKS() {

  hideQuestionUI();
  jQuery('#submitting').show();

  var answer = new Array();
  for (var i = 0; i < lksQuestion.length; i++) {

    var questionId = lksQuestion[i].id;
    var answerId = null;

    if (currentAnswers[questionId]) {
      answerId = currentAnswers[questionId];
    }

    if (answerId != null) {
      answer.push({'index': (i + 1), 'answerId': answerId});
    }
  }

  jQuery.ajax({
    type: 'POST',
    url: '../finish_lks.php',
    async: false,
    data: {lr_id: lrId, lks_id: lksId},
    success: function(data)
    {
      if (data == 'success'){
        //par = array('parLr' => data.lr_id, 'parLks' => data.lks_id);
        document.location.href = '../submit_lks/' + lksId + '/' + lrId; //??
      }
      else{
        jQuery('#submitting').hide();
        showError(data);
      }
    }
  });

}

function showError(m) {

  jQuery('#error-text').text(m);
  jQuery('#error-message').show();

}

function updateTimer() {

  var timeLeft = TIME_LEFT - elapsedTime;
  elapsedTime += 1;

  var minutes = Math.floor(timeLeft / 60);
  var seconds = timeLeft % 60;
  var hours = Math.floor(minutes / 60);
  var minutes = minutes % 60;

  if (hours < 10) { hours = '0' + hours; }
  if (minutes < 10) { minutes = '0' + minutes; }
  if (seconds < 10) { seconds = '0' + seconds; }


  if (timeLeft <= 0) {

    alert('Waktu mengerjakan telah habis. Jawaban anda akan disimpan.');

    // If we're in ajax mode, submit via ajax - otherwise, redirect to the completion page
    if (jQuery('#question-ui').length) {
      finishLKS(false);
    } else {
      document.location.href = 'complete.php?id=' + LKS_ID;
    }

  } else {
    jQuery('#exam-time-left').val(hours + ':' + minutes + ':' + seconds);
    setTimeout('updateTimer()', 1000);
  }

}

//form tags to omit in NS6+:
var omitformtags = ['input', 'textarea', 'select'];

omitformtags = omitformtags.join('|');

function disableselect(e) {
if (omitformtags.indexOf(e.target.tagName.toLowerCase()) == -1)
return false;
}

function reEnable() {
return true;
}

if (typeof document.onselectstart != 'undefined')
document.onselectstart = new Function('return false');
else {
document.onmousedown = disableselect;
document.onmouseup = reEnable;
}