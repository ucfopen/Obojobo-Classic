import './repository-page.scss'

import React, { useState, useCallback } from 'react'
import { useQuery, useQueryCache } from 'react-query'
import { apiGetInstances } from '../util/api'
import MyInstances from './my-instances'
import InstanceSection from './instance-section'
import Header from './header'

const RepositoryPage = () => {
	const queryCache = useQueryCache()
	const reloadInstances = useCallback(() => {
		queryCache.invalidateQueries('getInstances')
		setSelectedInstanceIndex(null)
	}, null)
	const { isError, data, error } = useQuery('getInstances', apiGetInstances, {
		initialStale: true,
		staleTime: 0
	})
	const [selectedInstanceIndex, setSelectedInstanceIndex] = useState(null)

	if (isError) return <span>Error: {error.message}</span>

	const onClickAboutThisLO = () => {
		alert('onClickAboutThisLO')
	}

	const onClickEditInstanceDetails = () => {
		alert('onClickEditInstanceDetails')
	}

	const onClickManageAccess = () => {
		alert('onClickManageAccess')
	}

	const onClickDownloadScores = () => {
		alert('onClickDownloadScores')
	}

	const onClickViewScoresByQuestion = () => {
		alert('onClickViewScoresByQuestion')
	}

	const onClickHeaderAboutOrBannerLink = () => {
		alert('onClickHeaderAboutOrBannerLink')
	}

	const onClickHeaderCloseBanner = () => {
		alert('onClickHeaderCloseBanner')
	}

	const onClickLogOut = () => {
		alert('onClickLogOut')
	}

	const onClickPreview = () => {
		if (selectedInstanceIndex === null) {
			return
		}

		const selectedInstance = data[selectedInstanceIndex]

		window.open(`/preview/${selectedInstance.loID}`, '_blank')
	}

	return (
		<div className="repository">
			<Header
				onClickAboutOrBannerLink={onClickHeaderAboutOrBannerLink}
				onClickCloseBanner={onClickHeaderCloseBanner}
				onClickLogOut={onClickLogOut}
			/>
			<main>
				<div className="wrapper">
					<MyInstances
						instances={data}
						selectedInstanceIndex={selectedInstanceIndex}
						onSelect={row => setSelectedInstanceIndex(row)}
						onClickRefresh={() => reloadInstances()}
					/>
					<InstanceSection
						onClickAboutThisLO={onClickAboutThisLO}
						onClickPreview={onClickPreview}
						onClickEditInstanceDetails={onClickEditInstanceDetails}
						onClickManageAccess={onClickManageAccess}
						onClickDownloadScores={onClickDownloadScores}
						onClickViewScoresByQuestion={onClickViewScoresByQuestion}
						instance={selectedInstanceIndex !== null ? data[selectedInstanceIndex] : null}
					/>
				</div>
			</main>
		</div>
	)
}

export default RepositoryPage
