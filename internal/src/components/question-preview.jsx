import './question-preview.scss'

import React from 'react'
import PropTypes from 'prop-types'
import FlashHTML from './flash-html'
import MediaView from './media-view'

const renderQuestionBody = items => {
	return items.map((item, index) => {
		switch (item.component) {
			case 'TextArea':
				return (
					<div key={index} className="text-area">
						<FlashHTML value={item.data} />
					</div>
				)

			case 'MediaView':
				return <MediaView key={index} media={item.media[0]} />
		}

		return null
	})
}

const getWeightType = weight => {
	switch (weight) {
		case 100:
			return 'correct'

		case 0:
			return 'incorrect'

		default:
			return 'partially-correct'
	}
}

const getAnswerChoiceIcon = weight => {
	switch (weight) {
		case 100:
			return '✓'

		case 0:
			return '×'

		default:
			return `${weight}%`
	}
}

const isStudentResponse = (itemType, response, answer) => {
	switch (itemType) {
		case 'MC':
			return response === answer.answerID

		case 'QA':
			return response === answer.answer
	}

	return false
}

const renderQuestionAnswers = ({ itemType, answers }, response = null) => {
	switch (itemType) {
		case 'MC': {
			return (
				<ol className="mc-answers">
					{answers.map((answer, index) => {
						const weightType = getWeightType(answer.weight)
						const studentResponse = isStudentResponse(itemType, response, answer)

						return (
							<li
								key={answer.answerID}
								className={`answer-choice is-weight-${weightType} ${
									studentResponse ? 'is-student-response' : 'is-not-student-response'
								}`}
							>
								<div className="students-response">
									{studentResponse ? "Student's response:" : null}
								</div>
								<div className="answer-choice-item">
									<span className="icon">{getAnswerChoiceIcon(answer.weight)}</span>
									<span className="answer-label">{String.fromCharCode(65 + index)}</span>
									<div className="answer-body">
										<FlashHTML value={answer.answer} />
									</div>
								</div>
							</li>
						)
					})}
				</ol>
			)
		}

		case 'QA':
			return (
				<div className="qa-answers">
					{response !== null ? (
						<div className="student-response-container">
							<b>Student response:</b> <span className="student-response">{`"${response}"`}</span>
						</div>
					) : null}
					<div className="correct-answers-list">
						<b>Correct answers:</b>
						<ul>
							{answers.map(answer => (
								<li key={answer.answerID}>{answer.answer}</li>
							))}
						</ul>
					</div>
				</div>
			)
	}
}

export default function QuestionPreview({ questionNumber, altNumber, question, score, response }) {
	return (
		<section className={`repository--question-preview is-type-${question.itemType}`}>
			<h1>
				Question {questionNumber}
				{altNumber > 1 ? ` (Alt ${String.fromCharCode(altNumber + 64)})` : ''}
			</h1>
			<div className="student-score">
				Student&apos;s Question Score: <b>{score}%</b>
			</div>
			{question.itemType === 'Media' ? (
				<div className="question-body">
					<MediaView media={question.items[0].media[0]} />
				</div>
			) : (
				<React.Fragment>
					<div
						className={`question-body ${
							question.items.length > 1 ? 'is-split-question' : 'is-not-split-question'
						}`}
					>
						{renderQuestionBody(question.items)}
					</div>
					<div className="question-answers">{renderQuestionAnswers(question, response)}</div>
				</React.Fragment>
			)}
		</section>
	)
}

QuestionPreview.defaultProps = {
	response: null
}

QuestionPreview.propTypes = {
	questionNumber: PropTypes.number.isRequired,
	altNumber: PropTypes.number.isRequired,
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
						itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3', 'youTube']),
						descText: PropTypes.string,
						width: PropTypes.number,
						height: PropTypes.number
					})
				)
			})
		)
	}).isRequired,
	score: PropTypes.number,
	response: PropTypes.string
}
