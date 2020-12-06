import './modal-instance-details.scss'

import React, { useState } from 'react'
import PropTypes from 'prop-types'
import HelpButton from './help-button'
import FormDateTime from './form-date-time'
import Button from './button'
import RepositoryModal from './repository-modal'

export default function ModalInstanceDetails(props) {
	const [name, setName] = useState(props.name)
	const [courseID, setCourseID] = useState(props.courseID)
	const [startTime, setStartTime] = useState(props.startTime)
	const [endTime, setEndTime] = useState(props.endTime)
	const [attemptCount, setAttemptCount] = useState(props.attemptCount)
	const [scoreMethod, setScoreMethod] = useState(props.scoreMethod)
	const [allowScoreImport, setAllowScoreImport] = useState(props.allowScoreImport)

	const [exScoreMethod, exFinalScore] = React.useMemo(() => {
		switch (scoreMethod) {
			case 'h':
				return ['Highest', 90]
			case 'm':
				return ['Average', 82]
			case 'r':
				return ['Last', 80]
			default:
				return ['', '']
		}
	}, [scoreMethod])

	const onSave = React.useCallback(() => {
		props.onSave({
			instID: props.instID,
			name,
			courseID,
			startTime,
			endTime,
			attemptCount,
			scoreMethod,
			allowScoreImport
		})
	}, [name, courseID, startTime, endTime, attemptCount, scoreMethod, allowScoreImport])

	return (
		<RepositoryModal
			className="instanceDetails"
			instanceName={props.instanceName}
			onCloseModal={props.onClose}
		>
			<div className="modal-instance-details">
				<h1>{`${props.mode === 'create' ? 'Create' : 'Edit'} Instance Details`}</h1>
				<div className="box">
					<div className="row">
						<span className="title">Instance Name:</span>
						<div className="flex-container">
							<input
								type="text"
								value={name}
								onChange={e => {
									setName(e.target.value)
								}}
							/>
							<HelpButton>
								<div>
									Your published instance will be displayed to students as the name you input here.
									By default this name is the same as the object name.
								</div>
							</HelpButton>
						</div>
					</div>
					<div className="row">
						<span className="title">Course Name:</span>
						<div className="flex-container">
							<input type="text" value={courseID} onChange={e => setCourseID(e.target.value)} />
							<HelpButton>
								<div>
									This field shows the course for this instance. This field is for your organization
									only - changing it won&apos;t impact how your instance functions.
								</div>
							</HelpButton>
						</div>
					</div>
				</div>
				<div className="box border">
					<div className="row">
						<span className={`title ${props.externalLink ? 'is-disabled' : 'is-not-disabled'}`}>
							Open Date:
						</span>
						<div className="flex-container">
							<FormDateTime value={props.externalLink ? null : startTime} onChange={setStartTime} />
							<HelpButton>
								{props.externalLink ? (
									<div>
										Since this instance is linked to an external course you cannot set the start
										date. Access to your module is reliant on settings in the external system.
									</div>
								) : (
									<div>
										This is the date when this instance will be opened to students. Before this
										date, students will not be able to access the instance.
									</div>
								)}
							</HelpButton>
						</div>
					</div>
					<div className="row">
						<span className={`title ${props.externalLink ? 'is-disabled' : 'is-not-disabled'}`}>
							Close Date:
						</span>
						<div className="flex-container">
							<FormDateTime value={props.externalLink ? null : startTime} onChange={setEndTime} />
							<HelpButton>
								{props.externalLink ? (
									<div>
										Since this instance is linked to an external course you cannot set the end date.
										Access to your module is reliant on settings in the external system.
									</div>
								) : (
									<div>
										This is the date when the assessment will be closed to students. After this
										date, students will not be able to take assessment attempts. They will still
										have access to the content and practice.
									</div>
								)}
							</HelpButton>
						</div>
					</div>
					<div className="row">
						{props.externalLink ? (
							<span className="linked">(Start/end dates are defined by the external system)</span>
						) : null}
					</div>
				</div>
				<div className="box">
					<div className="row">
						<span className="title">Attempts:</span>
						<div className="flex-container">
							<input
								type="number"
								value={attemptCount}
								min="1"
								max="255"
								onChange={e => setAttemptCount(parseInt(e.target.value, 10))}
								onBlur={() => setAttemptCount(Math.max(Math.min(attemptCount, 255), 1))}
							/>
							<HelpButton>
								<div>
									This is the number of tries a student will have to take the assessment quiz. If
									you provide more than one assessment attempt then the final score is determined by
									the &apos;Score Method&apos;. Students will be able to see how many attempts they
									have before they begin the assessment quiz.
								</div>
							</HelpButton>
						</div>
					</div>
					{attemptCount > 1 ? (
						<React.Fragment>
							<div className="row">
								<span className="title">Scoring:</span>
								<div className="flex-container">
									<select
										name="scoringMethod"
										value={scoreMethod}
										onChange={e => setScoreMethod(e.target.value)}
									>
										<option value="h">Take Highest Attempt</option>
										<option value="m">Take Average Score</option>
										<option value="r">Take Last Attempt</option>
									</select>
									<HelpButton>
										<div>
											This determines how the &apos;Final Score&apos; will be calculated by Obojobo
											for instances with more than one attempt. The student will be able to see how
											their score will be calculated before they begin the assessment quiz.
										</div>
									</HelpButton>
								</div>
							</div>
							<div className="row example">
								<span className="sub-title">Example:</span>
								<span>
									Given 3 attempts: 75, 90, 80. The{' '}
									<b>
										{exScoreMethod} score is: {exFinalScore}%
									</b>
								</span>
							</div>
						</React.Fragment>
					) : null}
					<div className="row">
						<div className="score-import">
							<label onClick={e => setAllowScoreImport(e.target.checked)}>
								<input type="checkbox" name="isImportAllowed" defaultChecked={allowScoreImport} />
								<span>Allow past scores to be imported</span>
							</label>
							<HelpButton>
								<div>
									This option allows students who have already taken this learning object to import
									their past highest attempt score instead of re-taking the object.
								</div>
							</HelpButton>
						</div>
					</div>
					<div className="buttons">
						<Button text="Cancel" type="alt" onClick={props.onClose} />
						<Button text="Save" type="small" onClick={onSave} />
					</div>
				</div>
			</div>
		</RepositoryModal>
	)
}

ModalInstanceDetails.defaultProps = {
	instID: null,
	name: '',
	courseID: '',
	startTime: null,
	endTime: null,
	attemptCount: 1,
	scoringMethod: 'h',
	allowScoreImport: true
}

ModalInstanceDetails.propTypes = {
	onClose: PropTypes.func.isRequired,
	onSave: PropTypes.func.isRequired,
	instID: PropTypes.number.isRequired,
	externalLink: PropTypes.bool.isRequired,
	name: PropTypes.string,
	courseID: PropTypes.string,
	startTime: PropTypes.number,
	endTime: PropTypes.number,
	attemptCount: PropTypes.number,
	scoreMethod: PropTypes.oneOf(['h', 'm', 'r']),
	allowScoreImport: PropTypes.bool
}
