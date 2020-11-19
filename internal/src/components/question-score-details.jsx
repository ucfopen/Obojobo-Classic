import React, { useState } from 'react'
import DefList from './def-list'
import PropTypes from 'prop-types'
import SearchField from './search-field'
import GraphResponses from './graph-responses'
import QuestionPreview from './question-preview'
import DataGridResponses from './data-grid-responses'
import './question-score-details.scss'

const MC = 'MC'
const QA = 'QA'
const MEDIA = 'Media'

const getFilteredResponses = (responses, query) => {
	if (!responses) {
		return []
	}

	return responses.filter(response => {
		if (!query) {
			return responses
		}

		query = query.toLowerCase()

		return (
			response.userName.toLowerCase().indexOf(query) > -1 ||
			response.response
				.toString()
				.toLowerCase()
				.indexOf(query) > -1 ||
			response.score.toString().indexOf(query) > -1
		)
	})
}

export default function QuestionScoreDetails(props) {
	const [query, setQuery] = useState('')
	const responses = props.responses
	const filteredResponses = getFilteredResponses(responses, query)
	const questionType = props.question.itemType
	let sum = 0,
		mean = 0,
		numCorrectAnswers = 0,
		foundCorrectAnswer = false

	const dataForGraph = []

	if (questionType === MC) {
		for (let i = 0; i < props.question.answers.length; i++) {
			dataForGraph.push({ label: String.fromCharCode(65 + i), value: 0, isCorrect: false })
		}

		// Processes the necessary data for the 'GraphResponses' component.
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
				}
			}
		}
	} else if (questionType === QA) {
		dataForGraph.push({ label: 'Incorrect', value: 0, isCorrect: false })
		dataForGraph.push({ label: 'Correct', value: 0, isCorrect: false })
	} else if (questionType === MEDIA) {
		dataForGraph.push({ label: '< 100', value: 0, isCorrect: false, score: 0 })
		dataForGraph.push({ label: '100', value: 0, isCorrect: true, score: 0 })
	}

	if (questionType === QA || questionType === MEDIA) {
		// Processes the necessary data for the 'GraphResponses' component.
		for (let i = 0; i < responses.length; i++) {
			if (responses[i].score === 100) {
				numCorrectAnswers++
				dataForGraph[1].value++
			} else {
				dataForGraph[0].value++
			}

			if (!foundCorrectAnswer) {
				if (responses[i].score === 100) {
					dataForGraph[1].isCorrect = true
					foundCorrectAnswer = true
				}
			}

			if (questionType === MEDIA) {
				dataForGraph[i].score = responses[i].score
			}
		}
	}

	// Calculates mean.
	for (let i = 0; i < dataForGraph.length; i++) {
		sum +=
			questionType === MC || questionType === QA ? dataForGraph[i].value : dataForGraph[i].score
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

	const getAccuracy = () => {
		return numCorrectAnswers / responses.length
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

	// items prop for DefList + response prop for QuestionPreview
	const items = [
		{
			label: '# Responses',
			value: getFormattedNumberOfResponses()
		}
	]

	if (questionType === MC) {
		items.push({ label: 'Std Dev', value: getStdDev() })
	} else if (questionType === QA) {
		items.push({ label: 'Accuracy', value: (getAccuracy() * 100).toString() + '%' })
	} else {
		items.push({ label: 'Mean', value: mean.toString() + '%' })
	}

	return (
		<div className="repository--question-score-details">
			<div className="data-and-responses-content">
				<div className="left-sidebar">
					<GraphResponses data={dataForGraph} width={300} height={300} />
					<DefList className="def-list" items={items} />
				</div>

				<div className="right-content">
					<header>
						<p>Student Responses</p>
						<SearchField
							placeholder={'Search for a name'}
							value={query}
							onChange={q => setQuery(q.target.value)}
						/>
					</header>
					<DataGridResponses responses={filteredResponses} />
				</div>
			</div>

			<div className="question-preview-container">
				<QuestionPreview className="question-preview" question={props.question} />
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
