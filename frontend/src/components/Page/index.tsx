import * as React from 'react';
import {Container, Typography} from "@material-ui/core";
import {makeStyles} from "@material-ui/core/styles";

const useStyles = makeStyles({
    title: {
        color: '#999999'
    }
})

type PageProps = {
    title: string
};
export const Page: React.FC<PageProps> = (props) => {
    const classes = useStyles();
    return (
        <div>
            <Container>
                <Typography className={classes.title}>
                    {props.title}
                </Typography>
            </Container>
        </div>
    );
};