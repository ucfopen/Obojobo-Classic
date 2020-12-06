import React from 'react'
import { Bar } from '@vx/shape'
import { Grid } from '@vx/grid'
import DefList from './def-list'
import { Group } from '@vx/group'
import PropTypes from 'prop-types'
import RefreshButton from './refresh-button'
import { AxisLeft, AxisBottom } from '@vx/axis'
import { scaleBand, scaleLinear } from '@vx/scale'
import './assessment-scores-summary.scss'

// @vx Accessors
const x = d => d.label
const y = d => d.value

const tickLabelPropsLeft = () => ({
	textAnchor: 'end'
})

const tickLabelPropsHoriz = () => ({
	textAnchor: 'middle'
})

export default function AssessmentScoresSummary(props) {
	const data = [
		{ label: '0-9', value: 0 },
		{ label: '10s', value: 0 },
		{ label: '20s', value: 0 },
		{ label: '30s', value: 0 },
		{ label: '40s', value: 0 },
		{ label: '50s', value: 0 },
		{ label: '60s', value: 0 },
		{ label: '70s', value: 0 },
		{ label: '80s', value: 0 },
		{ label: '90-100', value: 0 }
	]

	// Processes data for this component's graph once this component mounts.
	let sum = 0,
		lowestScore = 100,
		highestScore = 0
	const scores = props.scores

	for (let i = 0; i < scores.length; i++) {
		// Edge case:
		if (scores[i] === 100) {
			data[9].value++
			continue
		}
		data[Math.floor(scores[i] / 10)].value++

		// To calculate the mean.
		sum += scores[i]

		if (scores[i] < lowestScore) {
			lowestScore = scores[i]
		}

		if (scores[i] > highestScore) {
			highestScore = scores[i]
		}
	}

	const mean = sum / scores.length

	// Calculating standard deviation.
	let numerator = 0
	for (let i = 0; i < scores.length; i++) {
		numerator += Math.pow(scores[i] - mean, 2)
	}

	const items = [
		{
			label: 'Scores',
			value: scores?.length || '--'
		},
		{
			label: 'Mean',
			value: scores.length > 0 ? mean.toFixed(2) : '--'
		},
		{
			label: 'Std Dev',
			value:
				scores.length > 0
					? Math.sqrt(numerator / scores.length)
							.toFixed(2)
							.toString() + '%'
					: '--'
		},
		{
			label: 'Score Range',
			value: scores.length > 0 ? lowestScore.toString() + '-' + highestScore.toString() + '%' : '--'
		}
	]

	// Graph configurations
	const width = 500
	const height = 350

	// Bounds
	const xMax = width - 80
	const yMax = height - 80

	// Scales
	const xScale = scaleBand({
		range: [0, xMax],
		round: true,
		domain: data.map(x),
		padding: 0.2
	})

	const yScale = scaleLinear({
		range: [0, yMax],
		round: true,
		domain: [Math.max(...data.map(y)), 0]
	})

	return (
		<div className="assessment-scores-summary">
			<header>
				<p>Summary</p>
				<RefreshButton onClick={props.onClickRefresh} />
			</header>

			<div className="scores-summary">
				<span className="graph-container">
					<svg width={width} height={height}>
						<Group top={25} left={65}>
							<Grid
								left={0}
								xScale={xScale}
								yScale={yScale}
								width={xMax}
								height={yMax}
								numTicksRows={4}
								numTicksColumns={data.length}
								stroke="black"
								xOffset={xScale.bandwidth() / 2}
							/>
							<AxisLeft
								labelOffset={32}
								left={0}
								scale={yScale}
								numTicks={4}
								hideTicks={true}
								label="Frequency"
								labelProps={{}}
								tickLabelProps={tickLabelPropsLeft}
							/>
							{scores.length > 0
								? data.map(d => {
										const barWidth = xScale.bandwidth()
										const barHeight = yMax - yScale(d.value)
										return (
											<Bar
												key={`bar-${d.label}`}
												x={xScale(d.label)}
												y={yMax - barHeight}
												width={barWidth}
												height={barHeight}
												className="vx-bar"
											/>
										)
								  }) // eslint-disable-line no-mixed-spaces-and-tabs
								: null}
							<AxisBottom
								scale={xScale}
								label="Assessment Score %"
								hideTicks={true}
								labelOffset={20}
								top={yMax}
								labelProps={{}}
								tickLabelProps={tickLabelPropsHoriz}
								className="test"
							/>
						</Group>
					</svg>
				</span>
				<DefList items={items} className="def-list" />
			</div>
		</div>
	)
}

AssessmentScoresSummary.propTypes = {
	scores: PropTypes.arrayOf(PropTypes.number).isRequired,
	onClickRefresh: PropTypes.func.isRequired
}
