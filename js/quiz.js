// All the questions and options
var quizQuestions = []
var selectedQuestions = []
fetch('../class/getQuiz.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok')
        }
        return response.json()
    })
    .then(data => {
        quizQuestions = Array.isArray(data) ? data : []
        selectedQuestions = selectRandomQuestions([...quizQuestions], 10)
    })
    .catch(error => {
        console.error('' + error + '', error)
    })

var currentQuestionIndex = 0
var timer
var answersByQuizId = {}

function updateProgress() {
    const progressElement = document.getElementById('quizProgress')
    progressElement.textContent = `Question ${currentQuestionIndex + 1} of ${
        selectedQuestions.length
    }`
}

// Shuffle and select 10 random questions with shuffled options
function selectRandomQuestions(questions, count) {
    shuffleArray(questions)
    const takeCount = Math.min(count, questions.length)
    return questions.slice(0, takeCount).map(question => {
        // Keep track of the original option index so the server can score
        // reliably even when options are shuffled for display.
        const optionObjects = (Array.isArray(question.options) ? question.options : []).map(
            (text, originalIndex) => ({text, originalIndex})
        )
        let shuffledOptions = shuffleArray(optionObjects)
        return {...question, shuffledOptions}
    })
}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[array[i], array[j]] = [array[j], array[i]]
    }
    return array
}

function displayCurrentQuestion() {
    clearInterval(timer)
    if (!selectedQuestions || selectedQuestions.length === 0) {
        return
    }
    const question = selectedQuestions[currentQuestionIndex]
    const questionContainer =
        document.getElementsByClassName('question-container')[0]
    questionContainer.setAttribute('id', `question-${question.id}`)
    questionContainer.style.display = 'block'
    renderQuestion(questionContainer, question)
    startTimer()
    updateProgress()
    enableNextButton(false)
}

function renderQuestion(container, question) {
    container.innerHTML = ''

    const title = document.createElement('h3')
    title.textContent = question.question
    container.appendChild(title)

    const list = document.createElement('ul')
    const groupName = `answer-${question.id}`

    question.shuffledOptions.forEach((option, index) => {
        const li = document.createElement('li')

        const optionId = `option-${question.id}-${index}`
        const input = document.createElement('input')
        input.type = 'radio'
        input.id = optionId
        input.name = groupName
        input.value = String(option.originalIndex)

        input.addEventListener('change', () => {
            answerSelected(question.id, option.originalIndex, li, groupName)
        })

        const label = document.createElement('label')
        label.htmlFor = optionId
        label.textContent = option.text

        li.appendChild(input)
        li.appendChild(label)
        list.appendChild(li)
    })

    container.appendChild(list)
}

function answerSelected(questionId, selectedIndex, selectedListItem, groupName) {
    clearInterval(timer)
    // Store the chosen option index relative to the DB's unshuffled option list.
    answersByQuizId[String(questionId)] = selectedIndex

    const optionsList = document.querySelectorAll(`input[name='${groupName}']`)
    optionsList.forEach(option => (option.disabled = true))

    // Keep existing visual feedback behavior minimal: mark the selected option.
    // Correctness is computed server-side.
    selectedListItem.classList.add('selected-answer')

    enableNextButton(true)
}

function enableNextButton(enabled) {
    const nextButton = document.getElementById('nextQuestion')
    nextButton.disabled = !enabled
}

// The timer and its implementation
function startTimer() {
    let timeLeft = 15
    const timerElement = document.getElementById('timer')
    timerElement.textContent = `Time left: ${timeLeft} seconds`

    timer = setInterval(() => {
        timeLeft--
        timerElement.textContent = `Time left: ${timeLeft} seconds`

        if (timeLeft <= 0) {
            clearInterval(timer)
            timerElement.textContent = 'Time is up!'
            const question = selectedQuestions[currentQuestionIndex]
            const key = String(question.id)
            if (!(key in answersByQuizId)) {
                answersByQuizId[key] = null
            }
            goToNextQuestion()
        }
    }, 1000)
}

function goToNextQuestion() {
    if (currentQuestionIndex < selectedQuestions.length - 1) {
        currentQuestionIndex++
        displayCurrentQuestion()
    } else {
        clearInterval(timer)
        displayResults()
    }
}

// To display results
function displayResults() {
    const quizResults = document.getElementById('quiz-results')
    quizResults.innerHTML = `<h3>Quiz Completed!</h3><p>Saving results...</p>`
    quizResults.style.display = 'block'
    document.getElementById('quizForm').style.display = 'none';

    const answers = selectedQuestions.map(q => {
        const key = String(q.id)
        const selectedIndex = Object.prototype.hasOwnProperty.call(answersByQuizId, key)
            ? answersByQuizId[key]
            : null
        return {quizId: q.id, selectedIndex}
    })

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
    const csrf_token = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : ''

    fetch('../class/postQuizResult.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({answers, csrf_token}),
    })
        .then(response => response.json())
        .then(data => {
            if (data && data.status === 'success') {
                quizResults.innerHTML = `<h3>Quiz Completed!</h3>
                    <p>Correct Answers: ${data.correctCount}</p>
                    <p>Wrong Answers: ${data.wrongCount}</p>
                    <p>Incomplete Answers: ${data.incompleteCount}</p>`
            } else {
                quizResults.innerHTML = `<h3>Quiz Completed!</h3><p>Could not save results.</p>`
            }
        })
        .catch(error => {
            console.error('Error saving data:', error);
            quizResults.innerHTML = `<h3>Quiz Completed!</h3><p>Could not save results.</p>`
        });
}

// Starting the quiz
function initializeQuiz() {
    currentQuestionIndex = 0
    answersByQuizId = {}
    document.getElementById('quizForm').style.display = 'block'
    document.getElementById('quiz-results').style.display = 'none'
    selectedQuestions = selectRandomQuestions([...quizQuestions], 10)
    if (!selectedQuestions || selectedQuestions.length === 0) {
        const quizResults = document.getElementById('quiz-results')
        quizResults.innerHTML = `<h3>No quiz questions available.</h3>`
        quizResults.style.display = 'block'
        document.getElementById('quizForm').style.display = 'none'
        return
    }
    displayCurrentQuestion()
    document.getElementById('nextQuestion').style.display = 'block'
    document.getElementById('startQuiz').textContent = 'Restart Quiz'
}

document.getElementById('startQuiz').addEventListener('click', initializeQuiz)

document.getElementById('quizForm').addEventListener('click', event => {
    if (event.target && event.target.id === 'nextQuestion') {
        goToNextQuestion()
    }
})

// Set the current year in the footer
document.getElementById('currentYear').textContent = new Date().getFullYear()
