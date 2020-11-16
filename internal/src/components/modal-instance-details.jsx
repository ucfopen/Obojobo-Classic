import React, { useState } from 'react'
import PropTypes from 'prop-types'
import HelpButton from './help-button'
import FormDateTime from './form-date-time'
import Button from './button'

import './modal-instance-details.scss'

export default function ModalInstanceDetails(props) {
	const [instanceName, setInstanceName] = useState(props.instanceName)
	const [courseName, setCourseName] = useState(props.courseName)
	const [startDate, setStartDate] = useState(props.startDate)
	const [endDate, setEndDate] = useState(props.endDate)
	const [numAttempts, setNumAttempts] = useState(props.numAttempts)
	const [scoringMethod, setScoringMethod] = useState(props.scoringMethod)
	const [isImportAllowed, setIsImportAllowed] = useState(props.isImportAllowed)

	const renderExample = () => {
		if (scoringMethod === 'highest') {
			return (
				<div className="row example">
					<span className="sub-title">Take Highest Example:</span>
					<span>
						Score 1: 75%, Score 2: 90%, Score 3: 80%, <b>Final: 90%</b>
					</span>
				</div>
			)
		} else if (scoringMethod === 'average') {
			return (
				<div className="row example">
					<span className="sub-title">Take Average Example:</span>
					<span>
						Score 1: 75%, Score 2: 90%, Score 3: 80%, <b>Final: 81.67%%</b>
					</span>
				</div>
			)
		} else if (scoringMethod === 'last') {
			return (
				<div className="row example">
					<span className="sub-title">Take Last Example:</span>
					<span>
						Score 1: 75%, Score 2: 90%, Score 3: 80%, <b>Final: 80%</b>
					</span>
				</div>
			)
		}
	}

	const onSave = () => {
		const state = {
			instanceName,
			courseName,
			startDate,
			endDate,
			numAttempts,
			scoringMethod,
			isImportAllowed
		}

		props.onSave(state)
	}

	return (
		<div className="modal-instance-details">
			<h1>{`${props.mode === 'create' ? 'Create' : 'Edit'} Instance Details`}</h1>
			<div className="box">
				<div className="row">
					<span className="title">Instance Name:</span>
					<div className="flex-container">
						<input
							type="text"
							value={instanceName}
							onChange={event => setInstanceName(event.target.value)}
						/>
						<HelpButton />
					</div>
				</div>
				<div className="row">
					<span className="title">Course Name:</span>
					<div className="flex-container">
						<input
							type="text"
							value={courseName}
							onChange={event => setCourseName(event.target.value)}
						/>
						<HelpButton />
					</div>
				</div>
			</div>
			<div className="box border">
				<div className="row">
					<span className={`title ${props.isExternallyLinked ? 'is-disabled' : 'is-not-disabled'}`}>
						Start Date:
					</span>
					<div className="flex-container">
						<FormDateTime value={startDate} onChange={setStartDate} />
						<HelpButton />
					</div>
				</div>
				<div className="row">
					<span className={`title ${props.isExternallyLinked ? 'is-disabled' : 'is-not-disabled'}`}>
						End Date:
					</span>
					<div className="flex-container">
						<FormDateTime value={endDate} onChange={setEndDate} />
						<HelpButton />
					</div>
				</div>
				<div className="row">
					{props.isExternallyLinked ? (
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
							value={numAttempts}
							onChange={event => {
								if (event.target.value >= 0 && event.target.value < 256) {
									setNumAttempts(parseInt(event.target.value, 10))
								}
							}}
						/>
						<HelpButton />
					</div>
				</div>
				{numAttempts > 0 ? (
					<React.Fragment>
						<div className="row">
							<span className="title">Scoring:</span>
							<div className="flex-container">
								<select
									name="scoringMethod"
									value={scoringMethod}
									onChange={event => setScoringMethod(event.target.value)}
								>
									<option value="highest">Take Highest Attempt</option>
									<option value="average">Take Average Score</option>
									<option value="last">Take Last Attempt</option>
								</select>
								<HelpButton />
							</div>
						</div>
						{renderExample()}
					</React.Fragment>
				) : null}
				<div className="row">
					<div className="score-import">
						<label onClick={event => setIsImportAllowed(event.target.checked)}>
							<input type="checkbox" name="isImportAllowed" defaultChecked={isImportAllowed} />
							<span>Allow past scores to be imported</span>
						</label>
						<HelpButton />
					</div>
				</div>
				<div className="buttons">
					<Button text="Cancel" type="alt" onClick={props.onCancel} />
					<Button text="Save" type="small" onClick={onSave} />
				</div>
			</div>
		</div>
	)
}

ModalInstanceDetails.defaultProps = {
	instanceName: '',
	courseName: '',
	startDate: null,
	endDate: null,
	numAttempts: 1,
	scoringMethod: 'highest',
	isImportAllowed: true
}

ModalInstanceDetails.propTypes = {
	onCancel: PropTypes.func.isRequired,
	onSave: PropTypes.func.isRequired,
	isExternallyLinked: PropTypes.bool.isRequired,
	mode: PropTypes.oneOf(['create', 'edit']).isRequired,
	instanceName: PropTypes.string,
	courseName: PropTypes.string,
	startDate: PropTypes.oneOfType([null, PropTypes.instanceOf(Date)]),
	endDate: PropTypes.oneOfType([null, PropTypes.instanceOf(Date)]),
	numAttempts: PropTypes.number,
	scoringMethod: PropTypes.oneOf(['highest', 'average', 'last']),
	isImportAllowed: PropTypes.bool
}
