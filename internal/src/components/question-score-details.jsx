import React from 'react'
import DefList from './def-list'
import PropTypes from 'prop-types'
import SearchField from './search-field'
import GraphResponses from './graph-responses'
import QuestionPreview from './question-preview'
import DataGridResponses from './data-grid-responses'
import './question-score-details.scss'

export default function QuestionScoreDetails(props) {
	const responses = props.responses
	let sum = 0,
		mean = 0,
		numCorrectAnswers = 0,
		indexCorrectAnswer = -1

	const dataForGraph = [
		{ label: 'A', value: 0, isCorrect: false },
		{ label: 'B', value: 0, isCorrect: false },
		{ label: 'C', value: 0, isCorrect: false },
		{ label: 'D', value: 0, isCorrect: false }
	]

	// Processes the necessary data for the 'GraphResponses' component.
	let foundCorrectAnswer = false

	for (let i = 0; i < responses.length; i++) {
		const currAnswerChoice = responses[i].response.charCodeAt(0) - 65

		dataForGraph[currAnswerChoice].value++

		// To find the number of correct answers.
		if (responses[i].score === 100) {
			numCorrectAnswers++
		}

		// To find which answer is the correct one.
		if (!foundCorrectAnswer) {
			if (responses[i].score === 100) {
				dataForGraph[currAnswerChoice].isCorrect = true
				foundCorrectAnswer = true
				indexCorrectAnswer = i
			}
		}
	}

	// Calculates mean.
	for (let i = 0; i < dataForGraph.length; i++) {
		sum += dataForGraph[i].value
	}
	mean = sum / dataForGraph.length

	const getStdDev = () => {
		let numerator = 0
		for (let i = 0; i < responses.length; i++) {
			const diff = dataForGraph[responses[i].response.charCodeAt(0) - 65].value - mean
			numerator += Math.pow(diff, 2)
		}

		return Math.sqrt(numerator / responses.length)
			.toFixed(2)
			.toString()
	}

	const getFormattedNumberOfResponses = () => {
		return (
			responses.length.toString() +
			' (' +
			numCorrectAnswers.toString() +
			' Correct, ' +
			(responses.length - numCorrectAnswers).toString() +
			' Incorrect)'
		)
	}

	// Items prop for DefList.
	const items = [
		{
			label: '# Responses',
			value: getFormattedNumberOfResponses()
		},
		{
			label: 'Std Dev',
			value: getStdDev()
		}
	]

	// Response prop for QuestionPreview.
	let response = ''
	const question = props.question
	switch (question.itemType) {
		case 'MC':
			response = indexCorrectAnswer !== -1 ? question.answers[indexCorrectAnswer].answerID : ''
			break
		case 'QA':
			break
		case 'Media':
			break
	}

	return (
		<div className="question-score-details">
			<div className="data-and-responses-content">
				<div className="left-sidebar">
					<GraphResponses data={dataForGraph} width={300} height={300} />
					<DefList className="def-list" items={items} />
				</div>

				<div className="right-content">
					<header>
						<p>Student Responses</p>
						<SearchField placeholder={'Search for a name'} />
					</header>
					<DataGridResponses responses={props.responses} />
				</div>
			</div>

			<div className="question-preview-container">
				<QuestionPreview
					className="question-preview"
					question={props.question}
					response={response}
				/>
			</div>
		</div>
	)
}

QuestionScoreDetails.propTypes = {
	question: PropTypes.shape({
		itemType: PropTypes.oneOf(['MC', 'QA', 'Media']),
		answers: PropTypes.arrayOf(
			PropTypes.shape({
				answerID: PropTypes.string,
				answer: PropTypes.string,
				weight: PropTypes.number
			})
		),
		items: PropTypes.arrayOf(
			PropTypes.shape({
				component: PropTypes.oneOf(['TextArea', 'MediaView']),
				data: PropTypes.string,
				media: PropTypes.arrayOf(
					PropTypes.shape({
						mediaID: PropTypes.number,
						title: PropTypes.string,
						itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3']),
						descText: PropTypes.string,
						width: PropTypes.number,
						height: PropTypes.number
					})
				)
			})
		)
	}).isRequired,
	responses: PropTypes.arrayOf(
		PropTypes.shape({
			userName: PropTypes.string,
			response: PropTypes.string,
			score: PropTypes.number,
			time: PropTypes.number
		})
	).isRequired
}
