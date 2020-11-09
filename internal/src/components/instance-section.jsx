import './instance-section.scss'

import React from 'react'
import PropTypes from 'prop-types'
import Button from './button'
import SectionHeader from './section-header'
import DefList from './def-list'
import AssessmentScoresSummary from './assessment-scores-summary'
import SearchField from './search-field'
import DataGridAssessmentScores from './data-grid-assessment-scores'
import AssessmentScoresSection from './assessment-scores-section'
import HelpButton from './help-button'
import InstructionsFlag from './instructions-flag'
import dayjs from 'dayjs'
import humanizeDuration from 'humanize-duration'

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

export default function InstanceSection({
	instance,
	scores,
	onClickAboutThisLO,
	onClickPreview,
	onClickEditInstanceDetails,
	onClickManageAccess,
	onClickDownloadScores,
	onClickViewScoresByQuestion,
	onClickRefreshScores
}) {
	if (!instance) {
		return (
			<div className="repository--instance-section is-empty">
				<InstructionsFlag text="Select an instance from the left" />
			</div>
		)
	}

	const startTime = dayjs(instance.startTime * 1000)
	const endTime = dayjs(instance.endTime * 1000)

	return (
		<div className="repository--instance-section is-not-empty">
			<div className="header">
				<div className="main-info">
					<h1>{instance.name}</h1>
					<h2>{`Course: ${instance.courseID}`}</h2>
				</div>
				<Button onClick={onClickAboutThisLO} type="text" text="About this learning object..." />
				<Button onClick={onClickPreview} type="large" text="Preview" />
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
							label: 'Instance Open',
							value: instance.externalLink ? '--' : startTime.format('MM/DD/YY - hh:mm A') + ' EST'
						},
						{
							label: 'Close',
							value: instance.externalLink ? '--' : endTime.format('MM/DD/YY - hh:mm A') + ' EST'
						},
						{
							value: instance.externalLink
								? '(This instance is being used in an external system)'
								: getDurationText(startTime, endTime)
						},
						{ label: 'Attempts', value: instance.attemptCount },
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
				<span>(@TODO - Put ownership text here)</span>
				<Button onClick={onClickManageAccess} type="small" text="Manage access" />
			</div>

			<AssessmentScoresSection
				onClickDownloadScores={onClickDownloadScores}
				assessmentScores={scores}
				onClickRefresh={onClickRefreshScores}
			/>

			<hr />

			<div className="scores-by-question">
				<h4>Scores by question</h4>
				<Button onClick={onClickViewScoresByQuestion} type="small" text="View..." />
			</div>
		</div>
	)
}

InstanceSection.propTypes = {
	instance: PropTypes.oneOfType([null, PropTypes.object]),
	scores: PropTypes.oneOfType([null, PropTypes.object]),
	onClickDownloadScores: PropTypes.func.isRequired,
	onClickEditInstanceDetails: PropTypes.func.isRequired,
	onClickManageAccess: PropTypes.func.isRequired,
	onClickViewScoresByQuestion: PropTypes.func.isRequired,
	onClickAboutThisLO: PropTypes.func.isRequired,
	onClickPreview: PropTypes.func.isRequired,
	onClickRefreshScores: PropTypes.func.isRequired
}
