import React from 'react'
import PropTypes from 'prop-types'
import { Bar } from '@vx/shape';
import { Group } from "@vx/group"
import { scaleBand, scaleLinear } from "@vx/scale"
import { AxisLeft, AxisBottom } from "@vx/axis"
import { Grid } from '@vx/grid'
import './graph-responses.scss'

// accessors return the label and value of that data item
const x = d => d.label
const y = d => d.value

const tickLabelPropsLeft = () => ({
	textAnchor: 'end'
})

const tickLabelPropsHoriz = () => ({
	textAnchor: 'middle'
})

export default function GraphResponses({ data, width, height }) {
	// bounds
	const xMax = width - 80
	const yMax = height - 80

	// scales
	const xScale = scaleBand({
		range: [0, xMax],
		round: true,
		domain: data.map(x),
		padding: 0.2,
	})

	const yScale = scaleLinear({
		range: [0, yMax],
		round: true,
		domain: [Math.max(...data.map(y)), 0],
	})


	return (
		<span className="graph-responses">
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
						label="Number of Responses"
						labelProps={{}}
						tickLabelProps={tickLabelPropsLeft}
					/>
					{data.map((d) => {
						const barWidth = xScale.bandwidth()
						const barHeight = yMax - yScale(d.value)
						return <Bar
							key={`bar-${d.label}`}
							x={xScale(d.label)}
							y={yMax - barHeight}
							width={barWidth}
							height={barHeight}
							className={d.isCorrect ? 'is-correct' : 'is-not-correct'}
						/>
					})}
					<AxisBottom
						scale={xScale}
						label="Answer Choice"
						hideTicks={true}
						labelOffset={20}
						top={yMax}
						labelProps={{}}
						tickLabelProps={tickLabelPropsHoriz}
					/>
				</Group>
			</svg>
		</span>
	)
}

GraphResponses.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			label: PropTypes.string,
			value: PropTypes.number,
			isCorrect: PropTypes.bool
		})
	),
	height: PropTypes.number,
	width: PropTypes.number
}
