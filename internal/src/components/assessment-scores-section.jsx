import './assessment-scores-section.scss'
import React from 'react'
import PropTypes from 'prop-types'
import SectionHeader from './section-header'
import AssessmentScoresSummary from './assessment-scores-summary'
import DataGridAssessmentScores from './data-grid-assessment-scores'
import Button from './button'
import SearchField from './search-field'

export default function AssessmentScoresSection(props) {
	const { assessmentScores, selectedStudentIndex, onSelect } = props
	const scores = assessmentScores
		.map(assessment => assessment.score.value)
		.filter(score => score !== null)

	return (
		<div className="repository--assessment-scores-section">
			<SectionHeader label="Assessment Scores" />
			<div className="assessment-section-body">
				<div className="assessment-scores-summary">
					<AssessmentScoresSummary scores={scores} />
				</div>
				<hr className="section-divider" />
				<div className="assessment-score-search">
					<p className="title">Scores by student</p>
					<SearchField placeholder="Search for a name" />
				</div>
				<DataGridAssessmentScores
					data={assessmentScores}
					selectedIndex={selectedStudentIndex}
					onSelect={onSelect}
				/>
				<div className="download-button">
					<Button type="large" text="Download these scores as a CSV file" />
				</div>
			</div>
		</div>
	)
}

AssessmentScoresSection.propTypes = {
	assessmentScores: PropTypes.arrayOf(
		PropTypes.shape({
			user: PropTypes.string.isRequired,
			score: PropTypes.shape({
				value: PropTypes.oneOfType([null, PropTypes.number]),
				isScoreImported: PropTypes.bool
			}).isRequired,
			lastSubmitted: PropTypes.oneOfType([null, PropTypes.string]).isRequired,
			attempts: PropTypes.shape({
				numAttemptsTaken: PropTypes.number.isRequired,
				numAdditionalAttemptsAdded: PropTypes.number.isRequired,
				numAttempts: PropTypes.number.isRequired,
				isAttemptInProgress: PropTypes.bool
			})
		})
	),
	selectedStudentIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired,
	onClickRefresh: PropTypes.func.isRequired
}
