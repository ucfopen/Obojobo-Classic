import './assessment-scores-section.scss'
import React, { useState } from 'react'
import PropTypes from 'prop-types'
import SectionHeader from './section-header'
import AssessmentScoresSummary from './assessment-scores-summary'
import DataGridAssessmentScores from './data-grid-assessment-scores'
import Button from './button'
import SearchField from './search-field'

export default function AssessmentScoresSection({
	instID,
	assessmentScores,
	onClickRefresh,
	onClickSetAdditionalAttempt,
	onClickScoreDetails,
	onClickDownloadScores
}) {
	const [search, setSearch] = useState('')

	// reset search filter when looking at a new instance
	React.useEffect(() => {
		setSearch('')
	}, [instID])

	// filter any null scores
	const scores = React.useMemo(() => {
		if (!assessmentScores) return []
		return assessmentScores.map(assessment => assessment.score).filter(score => score !== null)
	}, [assessmentScores])

	const assessmentScoresDataGridData = React.useMemo(() => {
		if (!assessmentScores) return null // loading
		if (search === '') return [...assessmentScores] // show all
		// filter by user name
		const lowerCaseSearch = search.toLowerCase()
		return assessmentScores.filter(
			assessment => assessment.user.toLowerCase().indexOf(lowerCaseSearch) > -1
		)
	}, [assessmentScores, search])

	const scoreCount = React.useMemo(() => assessmentScores?.length || 0)

	return (
		<div className="repository--assessment-scores-section">
			<SectionHeader label="Assessment Scores" />
			<div className="assessment-section-body">
				<div className="assessment-scores-summary">
					<AssessmentScoresSummary scores={scores} onClickRefresh={onClickRefresh} />
				</div>
				<hr className="section-divider" />
				<div className="assessment-score-search">
					<p className="title">Scores by student</p>
					<SearchField value={search} placeholder="Search for a name" onChange={setSearch} />
				</div>
				<DataGridAssessmentScores
					data={assessmentScoresDataGridData || null}
					rowCount={scoreCount}
					onClickSetAdditionalAttempt={onClickSetAdditionalAttempt}
					onClickScoreDetails={onClickScoreDetails}
				/>
				<div className="download-button">
					<Button
						onClick={onClickDownloadScores}
						type="small"
						text={`Download ${scoreCount} scores as a CSV file`}
						disabled={scoreCount < 1}
					/>
				</div>
			</div>
		</div>
	)
}

AssessmentScoresSection.propTypes = {
	instID: PropTypes.number.isRequired,
	assessmentScores: PropTypes.arrayOf(
		PropTypes.shape({
			user: PropTypes.string.isRequired,
			userID: PropTypes.string.isRequired,
			score: PropTypes.shape({
				value: PropTypes.number,
				isScoreImported: PropTypes.bool
			}).isRequired,
			lastSubmitted: PropTypes.number,
			attempts: PropTypes.shape({
				numAttemptsTaken: PropTypes.number.isRequired,
				additional: PropTypes.number.isRequired,
				numAttempts: PropTypes.number.isRequired,
				isAttemptInProgress: PropTypes.bool
			})
		})
	),
	onClickRefresh: PropTypes.func.isRequired,
	onClickDownloadScores: PropTypes.func.isRequired,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired,
	onClickScoreDetails: PropTypes.func.isRequired
}
