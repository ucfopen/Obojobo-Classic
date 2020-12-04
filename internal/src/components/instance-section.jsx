import './instance-section.scss'

import React from 'react'
import PropTypes from 'prop-types'
import Button from './button'
import SectionHeader from './section-header'
import DefList from './def-list'
import AssessmentScoresSection from './assessment-scores-section'
import HelpButton from './help-button'
import InstructionsFlag from './instructions-flag'
import dayjs from 'dayjs'
import humanizeDuration from 'humanize-duration'
import { ModalAboutLOWithAPI } from './modal-about-lo'
import ModalInstanceDetails from './modal-instance-details'
import { apiGetScoresForInstance, apiEditInstance, apiEditExtraAttempts } from '../util/api'
import { useQuery, useMutation, useQueryCache } from 'react-query'
import PeopleSearchDialog from './people-search-dialog'
import { ModalScoresByQuestionWithAPI } from './modal-scores-by-question'
import { ModalScoreDetailsWithAPI } from './modal-score-details'

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

const getScoringMethodText = scoreMethod => {
	switch (scoreMethod) {
		case 'h':
			return 'Highest Attempt'

		case 'm':
			return 'Attempt Average'

		case 'r':
			return 'Last Attempt'
	}
}

const getDurationText = (startTime, endTime) => {
	const now = dayjs()

	if (now.isAfter(endTime)) {
		return '(This instance is closed)'
	}

	if (now.isBefore(startTime)) {
		return 'This instance opens in ' + humanizeDuration(startTime - now, { largest: 2 })
	}

	return 'This instance closes in ' + humanizeDuration(endTime - now, { largest: 2 })
}

const noOp = () => {}

export default function InstanceSection({
	instance,
	usersWithAccess,
	user,
	setModal
}) {
	const queryCache = useQueryCache()
	const [mutateInstance] = useMutation(apiEditInstance)
	const [mutateExtraAttempts] = useMutation(apiEditExtraAttempts)

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

	// process scores for instance
	const scores = React.useMemo(() => {
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

	const onClickDownloadScoresWithUrl = React.useCallback(() => {
		if (!instance) return noOp
		const { instID, name, courseID, scoreMethod } = instance
		const instName = encodeURI(name.replace(/ /g, '_'))
		const courseName = encodeURI(courseID.replace(/ /g, '_'))
		const date = dayjs().format('MM-DD-YY')
		const url = `/assets/csv.php?function=scores&instID=${instID}&filename=${instName}_-_${courseName}_-_${date}&method=${scoreMethod}`
		window.open(url)
	}, [instance])

	const onClickRefreshScores = React.useCallback( () => {
		queryCache.refetchQueries(['getScoresForInstance', instance.instID], { exact: true })
	}, [instance])

	const onClickSetAdditionalAttempt = React.useCallback(async (userID, attempts) => {
		try {
			await mutateExtraAttempts({ userID, instID: instance.instID, newCount: attempts }, { throwOnError: true })
			onClickRefreshScores()
		} catch (e) {
			console.error('Error setting extra attempts')
			console.error(e)
		}
	}, [instance, mutateExtraAttempts, onClickRefreshScores])

	const onClickPreviewWithUrl = React.useCallback(() => {
		window.open(`/preview/${instance.loID}`, '_blank')
	}, [instance])

	const startTime = React.useMemo(() => dayjs(instance?.startTime * 1000), [instance?.startTime])
	const endTime = React.useMemo(() => dayjs(instance?.endTime * 1000), [instance?.endTime])

	const onClickAboutThisLO = React.useCallback(() => {
		setModal({
			component: ModalAboutLOWithAPI,
			className: 'aboutThisLO',
			props: {
				loID: instance.loID
			}
		})
	}, [instance])

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

	const onClickEditInstanceDetails = React.useCallback(() => {

		const onSave = async values => {
			try {
				await mutateInstance(values, { throwOnError: true })
				// update 'data' in place
				const keys = Object.keys(values)
				keys.forEach(k => {
					instance[k] = values[k]
				})

				// trying to populate cache with updated data, but no dice
				const data = queryCache.getQueryData(['getInstances'])
				const index = data.indexOf(instance)
				data[index] = {...instance}
				queryCache.setQueryData(['getInstances'], [...data])
				queryCache.refetchQueries(['getInstances'], { exact: true })
				// only way I can get the dang instance list to update

				setModal(null)
			} catch (error) {
				console.error('Error changing Instance Details')
				console.error(error)
			}
		}

		setModal({
			component: ModalInstanceDetails,
			className: 'instanceDetails',
			props: {...instance, onSave }
		})
	}, [instance, mutateInstance, setModal])

	const onClickManageAccess = React.useCallback(() => {
		setModal({
			component: PeopleSearchDialog,
			className: 'peopleSearch',
			props: {
				currentUserId: user.userID,
				clearPeopleSearchResults: () => {},
				onSelectPerson: () => {},
				onClose: () => {},
				onSearchChange: () => {},
				people: [{id: 5, avatarUrl: '/assets/images/user-circle.svg', firstName: 'Demo', lastName: 'man', username: 'demoman'}]
			}
		})
	}, [user, usersWithAccess])



	const onClickViewScoresByQuestion = React.useCallback(() => {
		setModal({
			component: ModalScoresByQuestionWithAPI,
			className: 'scoresByQuestion',
			props: {
				loID: instance.loID,
				instID: instance.instID
			}
		})
	}, [instance])

	if (!instance) {
		return (
			<div className="repository--instance-section is-empty">
				<InstructionsFlag text="Select an instance from the left" />
			</div>
		)
	}

	return (
		<div className="repository--instance-section is-not-empty">
			<div className="header">
				<div className="main-info">
					<h1>{instance.name}</h1>
					<h2>{`Course: ${instance.courseID}`}</h2>
				</div>
				<Button onClick={onClickAboutThisLO} type="text" text="About this learning object..." />
				<Button onClick={onClickPreviewWithUrl} type="large" text="Preview" />
			</div>

			<div className="link">
				<h3>Link</h3>
				{instance.externalLink ? (
					<div className="container">
						<input disabled type="text" value="--" />
						<HelpButton>
							<div>This instance is being used in an external system so no link is available</div>
						</HelpButton>
					</div>
				) : (
					<div className="container">
						<input readOnly type="text" value={`${window.origin}/view/${instance.instID}`} />
						<HelpButton>
							<div>
								Copy this link and distribute it to your students via online assignment, webcourses,
								course webpage or e-mail. Students will click on this ilnk to sign into Obojobo and
								take your instance.
							</div>
						</HelpButton>
					</div>
				)}
			</div>

			<SectionHeader label="Details" />
			<div className="details">
				<DefList
					items={[
						{
							label: 'Open Date',
							value: instance.externalLink ? '--' : startTime.format('MM/DD/YY - hh:mm A') + ' EST'
						},
						{
							label: 'Close Date',
							value: instance.externalLink ? '--' : endTime.format('MM/DD/YY - hh:mm A') + ' EST'
						},
						{
							value: instance.externalLink
								? '(This instance is being used in an external system)'
								: getDurationText(startTime, endTime)
						},
						{ label: 'Attempts Allowed', value: instance.attemptCount },
						{ label: 'Scoring Method', value: getScoringMethodText(instance.scoreMethod) },
						{
							label: 'Score Import',
							value: instance.allowScoreImport === '1' ? 'Enabled' : 'Disabled'
						}
					]}
				/>
				<Button onClick={onClickEditInstanceDetails} type="small" text="Edit Details" />
			</div>

			<SectionHeader label="Ownership &amp; Access" />
			<div className="ownership">
				<ul>
					{usersWithAccess.map(userItem => {
						return (
							<li key={userItem.userID}>{`${userItem.userString}${
								userItem.userID === user.userID ? ' (You)' : ''
							}`}</li>
						)
					})}
				</ul>
				<Button onClick={onClickManageAccess} type="small" text="Manage access" />
			</div>

			<AssessmentScoresSection
				instID={instance.instID}
				onClickDownloadScores={onClickDownloadScoresWithUrl}
				assessmentScores={scores}
				onClickRefresh={onClickRefreshScores}
				onClickSetAdditionalAttempt={onClickSetAdditionalAttempt}
				onClickScoreDetails={onClickScoreDetails}
			/>

			<Button onClick={onClickViewScoresByQuestion} type="small" text="Compare Scores by question..." />

		</div>
	)
}

InstanceSection.propTypes = {
	instance: PropTypes.object,
	scores: PropTypes.array,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired
}
