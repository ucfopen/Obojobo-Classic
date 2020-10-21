import React from 'react'


const LoadingIndicator =  ({isLoading = false}) => {
	if(isLoading) return <div>something is loading...</div>
	return <div>not loading</div>
}

export default LoadingIndicator
