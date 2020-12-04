import './assessment-scores-section.scss'
import React, { useState } from 'react'
import PropTypes from 'prop-types'
import SectionHeader from './section-header'
import AssessmentScoresSummary from './assessment-scores-summary'
import DataGridAssessmentScores from './data-grid-assessment-scores'
import Button from './button'
import SearchField from './search-field'
import dayjs from 'dayjs'
import { useQuery, useMutation, useQueryCache } from 'react-query'
import { apiGetScoresForInstance, apiEditExtraAttempts } from '../util/api'
import { ModalScoreDetailsWithAPI } from './modal-score-details'
const noOp = () => {}

const getFinalScoreFromAttemptScores = (scores, scoreMethod) => {
	switch (scoreMethod) {
		case 'h': // highest
			return Math.max.apply(null, scores)

		case 'r': // most recent
			return scores[scores.length - 1]

		case 'm': // average
			const sum = scores.reduce((acc, score) => acc + score, 0)
			return parseFloat(sum) / scores.length
	}

	return 0
}
const getUserString = n => {
	return `${n.last || 'unknown'}, ${n.first || 'name'}${n.mi ? ' ' + n.mi + '.' : ''}`
}

export default function AssessmentScoresSection({
	instance,
	setModal
}) {
	const [search, setSearch] = useState('')
	const queryCache = useQueryCache()
	const [mutateExtraAttempts] = useMutation(apiEditExtraAttempts)

	// reset search filter when looking at a new instance
	React.useEffect(() => {
		setSearch('')
	}, [instance])

	const { data, isFetching, error } = useQuery(
		['getScoresForInstance', instance?.instID],
		apiGetScoresForInstance,
		{
			initialStale: true,
			staleTime: Infinity,
			initialData: [],
			enabled: instance // load only after instance loads
		}
	)

	const onClickScoreDetails = React.useCallback((userName, userID) => {
		setModal({
			component: ModalScoreDetailsWithAPI,
			className: 'scoreDetails',
			props: {
				userName,
				userID,
				instID: instance.instID,
				loID: instance.loID
			}
		})
	}, [instance])

	const onClickRefresh = React.useCallback( () => {
		queryCache.refetchQueries(['getScoresForInstance', instance.instID], { exact: true })
	}, [instance])


	const onClickSetAdditionalAttempt = React.useCallback(async (userID, attempts) => {
		try {
			await mutateExtraAttempts({ userID, instID: instance.instID, newCount: attempts }, { throwOnError: true })
			onClickRefresh()
		} catch (e) {
			console.error('Error setting extra attempts')
			console.error(e)
		}
	}, [instance, mutateExtraAttempts, onClickRefresh])


	// process scores for instance
	const assessmentScores = React.useMemo(() => {
		if (!instance?.instID || isFetching) return null
		return data.map(u => {
			const lastAttempt = u.attempts[u.attempts.length - 1]
			const scores = u.attempts.map(a => a.score)
			const finished = u.attempts.filter(a => Boolean(a.submitDate))
			const lastSubmitted = finished[finished.length - 1]?.submitDate
			const score = getFinalScoreFromAttemptScores(scores, instance.scoreMethod)

			return {
				user: getUserString(u.user),
				userID: u.userID,
				score,
				isScoreImported: lastAttempt.linkedAttempt !== 0,
				lastSubmitted,
				numAttemptsTaken: u.attempts.length,
				additional: u.additional,
				attemptCount: instance.attemptCount,
				isAttemptInProgress: !lastAttempt.submitted
			}
		})
	}, [data, isFetching, instance])

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

	const scoreCount = React.useMemo(() => assessmentScores?.length || 0, [assessmentScoresDataGridData])

	const onClickDownloadScores = React.useCallback(() => {
		if (!instance) return noOp
		const { instID, name, courseID, scoreMethod } = instance
		const instName = encodeURI(name.replace(/ /g, '_'))
		const courseName = encodeURI(courseID.replace(/ /g, '_'))
		const date = dayjs().format('MM-DD-YY')
		const url = `/assets/csv.php?function=scores&instID=${instID}&filename=${instName}_-_${courseName}_-_${date}&method=${scoreMethod}`
		window.open(url)
	}, [instance])

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
	instance: PropTypes.object
}
